<?php

namespace Alaska\Domain;

class Comment
{
	/**
	 * Comment id.
	 * @var integer
	 */
	private $id;

	/**
	 * Comment author.
	 *
	 * @var string
	 */
	private $author;

	/**
	 * Comment content.
	 *
	 * @var string
	 */
	private $content;

	/**
	 * Associated billet.
	 *
	 * @var \Alaska\Domain\Billet
	 */
	private $billet;

	/**
	 * Parent comment.
	 *
	 * @var integer
	 */
	private $parent;

	/**
	 * Chidren comments.
	 *
	 * @var \Alaska\Domain\Comment
	 */
	private $children;

	/**
	 * Report id
	 *
	 * @var integer
	 */
	private $report;

	

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	public function getAuthor() {
		return $this->author;
	}

	public function setAuthor($author) {
		$this->author = $author;
		return $this;
	}

	public function getContent() {
		return $this->content;
	}

	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	public function getBillet() {
		return $this->billet;
	}

	public function setBillet(Billet $billet) {
		$this->billet = $billet;
		return $this;
	}

	public function getParent() {
		return $this->parent;
	}

	public function setParent($parent) {
		$this->parent = $parent;
		return $this;
	}

	public function getChildren() {
		return $this->children;
	}

	public function setChildren(array $children) {
		$this->children = $children;
		return $this;
	}

	public function getReport() {
		return $this->report;
	}
	
	public function setReport($report) {
		$this->report = $report;
		return $this;
	}

	

}