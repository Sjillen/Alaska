<?php

namespace Alaska\DAO;
	
use Alaska\Domain\Billet;

class BilletDAO extends DAO
{
	/**
	 * Return a list of all billets, sorted by date (most recent first).
	 *
	 * @return array A list of all billets.
	 */
	public function findAll() {
		$sql = "select * from t_billet order by billet_id desc";
		$result = $this->getDb()->fetchAll($sql);

		// Convert query result to an array of domain objects
		$billets = array();
		forEach ($result as $row) {
			$billetId = $row['billet_id'];
			$billets[$billetId] = $this->buildDomainObject($row);
		}
		return $billets;
	}

	/**
	 * Returns a billet matching the supplied id.
	 *
	 * @param integer $id
	 *
	 * @return \Alaska\Domain\Billet|throws an exception if no matching is found
	 */
	public function find($id) {
		$sql = "select * from t_billet where billet_id=?";
		$row = $this->getDb()->fetchAssoc($sql, array($id));

		if ($row)
			return $this->buildDomainObject($row);
		else
			throw new \Exception("No billet matching id " . $id);
	}

	/**
	 * Creates a Billet object based on a DB row.
	 *
	 * @param array $row The DB row containing Billet data.
	 * @return \Alaska\Domain\Billet
	 */

	protected function buildDomainObject(array $row) {
		$billet = new Billet();
		$billet->setId($row['billet_id']);
		$billet->setTitle($row['billet_title']);
		$billet->setContent($row['billet_content']);
		return $billet;
	}

	/**
	 * Saves a billet into the database
	 *
	 * @param \Alaska\Domain\Billet $billet The billet to save
	 */
	public function save(Billet $billet) {
		$billetData = array(
			'billet_title' => $billet->getTitle(),
			'billet_content' => $billet->getContent(),
			);

		if ($billet->getId()) {
			// The billet has already been saved : update it
			$this->getDb()->update('t_billet', $billetData, array('billet_id' => $billet->getId()));
		} else {
			// The billet has never been saved : insert it
			$this->getDb()->insert('t_billet', $billetData);
			// Get the id of the newly created billet and set it on the entity.
			$id = $this->getDb()->lastInsertId();
			$billet->setId($id);
		}
	}

	/**
	 * Removes a billet from the database.
	 *
	 * @param integer $id The billet id.
	 */
	public function delete($id) {
		//Delete the billet
		$this->getDb()->delete('t_billet', array('billet_id' => $id));
	}
}