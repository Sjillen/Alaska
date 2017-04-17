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
	 *@var \Alaska\DAO\UserDAO
	 */
	private $userDAO;
	public function setUserDAO(UserDAO $userDAO) {
		$this->userDAO = $userDAO;
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
		$sql = "select com_id, com_content, usr_id, parent_id from t_comment where billet_id=? order by com_id";
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
	 * Saves a comment into the database.
	 *
	 * @param \Alaska\Domain\Comment $comment The comment to save
	 */
	public function save (Comment $comment) {
		$commentData = array(
			'billet_id' 	=> $comment->getBillet()->getId(),
			'usr_id'		=> $comment->getAuthor()->getId(),
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
	 * Creates a Comment object based on a DB row.
	 *
	 * @param array $row The DB containing Comment data.
	 * @return \Alaska\Domain\Comment
	 */
	protected function buildDomainObject(array $row) {
		$comment = new Comment();
		$comment->setId($row['com_id']);
		$comment->setContent($row['com_content']);
		$comment->setParent($row['parent_id']);
		if(array_key_exists('billet_id', $row)) {
			// Find and set the associated billet
			$billetId = $row['billet_id'];
			$billet = $this->billetDAO->find($billetId);
			$comment->setBillet($billet);
		}
		if (array_key_exists('usr_id', $row)) {
			// Find the associated author
			$userId = $row['usr_id'];
			$user = $this->userDAO->find($userId);
			$comment->setAuthor($user);
		}
/*		if(array_key_exists('parent_id', $row)) {
			//Find and set the associated parent comment
			$parentId = $row['parent_id'];
			$parent = $this->commentDAO->find($parentId);
			$comment->setComment($parent);
		}
*/
		return $comment;
	}
}