<?php

namespace Alaska\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Alaska\Domain\Comment;
use Alaska\Form\Type\CommentType;


class HomeController {

	/**
	 * Home page controller.
	 *
	 * @param Application $app Silex application
	 */
	public function indexAction(Application $app) {
		$billets = $app['dao.billet']->findAll();
		return $app['twig']->render('index.html.twig', array('billets' => $billets));
	}

	/**
	 * Billet details controller.
	 *
	 * @param integer $id Billet id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */
	public function billetAction($id, Request $request, Application $app) {
		$billet = $app['dao.billet']->find($id);
		$commentFormView = null;
		$comment = new Comment();
		$comment->setBillet($billet);
		$commentForm = $app['form.factory']->create(CommentType::class, $comment);
		$commentForm->handleRequest($request);
		if ($commentForm->isSubmitted() && $commentForm->isValid()) {
			$app['dao.comment']->save($comment);
			$app['session']->getFlashBag()->add('success', 'Votre commentaire a été ajouté avec succès.');
		}
		$commentFormView = $commentForm->createView();
	
		$comments = $app['dao.comment']->findAllByBillet($id);

		return $app['twig']->render('billet.html.twig', array(
			'billet' => $billet,
			'comments' => $comments,
			'commentForm' => $commentFormView
			));
	}

	/**
	 * User login controller.
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */
	public function loginAction(Request $request, Application $app) {
		return $app['twig']->render('login.html.twig', array(
			'error'			=> $app['security.last_error']($request),
			'last_username'	=> $app['session']->get('security.last_username'),
		));
	}
}