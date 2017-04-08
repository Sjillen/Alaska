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
		$sql = "select com_id, com_content, usr_id from t_comment where billet_id=? order by com_id";
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
		return $comments;
	}

	/**
	 * Return a list of all comments for a parent comment, sorted by date ( most recent last).
	 *
	 * @param integer $parentId The parent id.
	 *
	 * @return array A list of all comments for the parent.
	 */
/*	public function findAllByParent($parentId) {
		//The associated parent is retrieved only once
		$parent = $this->parentDAO->find($parentId);

		//parent_id is not selected by the sql query
		// The parent won't be retrieved during domain object construction
		$sql = "select com_id, com_content, com_author from t_comment where parent_id=? order by com_id";
		$result = $this->getDb()->fetchAll($sql, array($parentId));

		// Convert query result to an array of domain objects
		$comments = array();
		forEach ($result as $row) {
			$comId = $row['com_id'];
			$comment = $this->buildDomainObject($row);
			// The associated parent is defined for the constructed comment
			$comment->setParent($parent);
			$comments[$comId] = $comment;
		}
		return $comments;
	}
*/
	/**
	 * Returns a comment matching the supplied id.
	 *
	 * @param integer $id
	 *
	 * @return \Alaska\Domain\Comment|throws an exception if no matching is found
	 */
/*	public function find($id) {
		$sql = "select * from t_comment where com_id=?";
		$row = $this->getDb()->fetchAssoc($sql, array($id));

		if ($row)
			return $this->buildDomainObject($row);
		else
			throw new \Exception("No comment matching id " . $id);
	}
*/
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