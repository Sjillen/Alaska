<?php
namespace Alaska\DAO;
use Alaska\Domain\Comment;
class CommentDAO extends DAO
{
	/**
	 * @var \Alaska\DAO\BilletDAO
	 */
	private $billetDAO;
	public function setBilletDAO(BilletDAO $billetDAO) {
		$this->billetDAO = $billetDAO;
	}
	/**
	 * @var \Alaska\DAO\CommentDAO
	 */
	private $parentDAO;
	public function setParentDAO(CommentDAO $parentDAO) {
		$this->parentDAO = $parentDAO;
	}
	


	/**
	 * Returns a list of all comment, sorted by date (most recent first).
	 *
	 * @return array A list of all comments.
	 */
	public function findAll() {
		$sql = "select * from t_comment order by com_id desc";
		$result = $this->getDb()->fetchAll($sql);

		//Convert query result to an array of domain objects
		$entities = array();
		foreach ($result as $row) {
			$id = $row['com_id'];
			$entities[$id] = $this->buildDomainObject($row);
		}
		return $entities;
	}


	/**
	* Return a list of all comments for a billet, sorted by date (most recent last).
	*
	* @param integer $billetId The billet id.
	*
	* @return array A list of all comments for the billet.
	*/
	public function findAllByBillet($billetId) {
		//The associated billet is retrieved only once
		$billet = $this->billetDAO->find($billetId);
		//billet_id is not selected by the sql query
		// The billet won't be retrieved during domain objet construction
		$sql = "select com_id, com_author, com_content, parent_id from t_comment where billet_id=? order by com_id";
		$result = $this->getDb()->fetchAll($sql, array($billetId));
		// Convert query result to an array of domain objects
		$comments = array();
		foreach ($result as $row) {
			$comId = $row['com_id'];
			$comment = $this->buildDomainObject($row);
			// The associated billet is defined for the constructed comment
			$comment->setBillet($billet);
			$comments[$comId] = $comment;		
		}

		$parentComments = array_filter($comments, function($comment) {
			return $comment->getParent() == NULL;
		});

		foreach ($parentComments as $parentComment) {
			$this->findChildComments($comments, $parentComment);
		}
		return $parentComments;
	}

	/**
	 * find the children comments of a comment
	 *
	 * @param array A list of comments and the parent comment
	 */
	 public function findChildComments (array $comments, Comment $parentComment) {
	 	$childrenComments = array_filter($comments, function($childComment) use ($parentComment) {
	 		return $childComment->getParent() == $parentComment->getId();
	 	});
	 	$parentComment->setChildren($childrenComments);
	 	foreach ($childrenComments as $childComment) {
	 		$this->findChildComments($comments, $childComment);
	 	}
	 } 

	 /**
	  * Removes all comments for a billet
	  *
	  * @param $billetId The id of the billet
	  */
	 public function deleteAllByBillet($billetId) {
	 	$this->getDb()->delete('t_comment', array('billet_id' => $billetId));
	 }

	

	 /**
	  * Removes all comments for a parent
	  *
	  * @param $parentId The id of the parent comment
	  */
	public function deleteAllByParent($parentId) {
		$this->getDb()->delete('t_comment', array('parent_id' => $parentId));
	}

	/**
	 * Saves a comment into the database.
	 *
	 * @param \Alaska\Domain\Comment $comment The comment to save
	 */
	public function save (Comment $comment) {
		$commentData = array(
			'billet_id' 	=> $comment->getBillet()->getId(),
			'com_author'		=> $comment->getAuthor(),
			'com_content'	=> $comment->getContent()
			);
		if ($comment->getId()) {
			//The comment has been already saved : update it
			$this->getDb()->update('t_comment', $commentData, array('com_id' => $comment->getId()));
		} else {
			// The comment has never been saved : insert it
			$this->getDb()->insert('t_comment', $commentData);
			//Get the id of the newly created comment and set it on the entity.
			$id = $this->getDb()->lastInsertId();
			$comment->setId($id);
		}
	}

	/**
	 *returns a comment matching the supplied id.
	 *
	 * @param integer $id The comment id
	 *
	 * @return \Alaska\Domain\Comment|throws an exception if no matching comment is found
	 */
	public function find($id) {
		$sql = "select * from t_comment where com_id=?";
		$row = $this->getDb()->fetchAssoc($sql, array($id));

		if($row)
			return $this->buildDomainObject($row);
		else
			throw new \Exception("Aucun commentaire correspondant a l'id " . $id);
	}

	/**
	 *Removes a comment from the database.
	 *
	 * @param integer $id The comment id
	 */
	public function delete($id) {
		// Delete the comment
		$this->getDb()->delete('t_comment', array('com_id' => $id));
	}

	/**
	 * Creates a Comment object based on a DB row.
	 *
	 * @param array $row The DB containing Comment data.
	 * @return \Alaska\Domain\Comment
	 */
	protected function buildDomainObject(array $row) {
		$comment = new Comment();
		$comment->setId($row['com_id']);
		$comment->setAuthor($row['com_author']);
		$comment->setContent($row['com_content']);
		$comment->setParent($row['parent_id']);

		if(array_key_exists('billet_id', $row)) {
			// Find and set the associated billet
			$billetId = $row['billet_id'];
			$billet = $this->billetDAO->find($billetId);
			$comment->setBillet($billet);
		}
		

		return $comment;
	}
}