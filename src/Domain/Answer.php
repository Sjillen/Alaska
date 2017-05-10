<?php

namespace Alaska\Domain;

use Doctrine\Common\Collections\ArrayCollection;

class Answer
{
	protected $response;

	protected $answers;

	public function __construct()
	{
		$this->answers = new ArrayCollection();
	}

	public function getResponse()
	{
		return $this->response;
	}

	public function setReponse($reponse)
	{
		$this->response = $response;
	}

	public function getAnswers()
	{
		return $this->answers;
	}
}