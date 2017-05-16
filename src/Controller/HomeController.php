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
	
		$comments = $app['dao.comment']->findAllByBillet($id);

		$comment = new Comment();
		$comment->setBillet($billet);

		$form = $app['form.factory']->create(CommentType::class, $comment);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$app['dao.comment']->save($comment);
			$app['session']->getFlashBag()->add('success', 'Votre commentaire a été ajouté avec succès.');
		}
		$commentFormView = $form->createView();
		

		return $app['twig']->render('billet.html.twig', array(
			'billet' => $billet,
			'comments' => $comments,
			'commentForm' => $commentFormView
			));
	}

	/**
	 * Comment answer controller.
	 *
	 * @param integer $id Comment id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */
	public function addCommentAction($idParent, $billetId, Request $request, Application $app)
	{
		$billet = $app['dao.billet']->find($billetId);
		$comment = new Comment();
		$comment->setBillet($billet);
		if ($idParent != 'null')
		{
			
			$comment->setAuthor($request->get('answerFormPseudo'.$idParent));
			$comment->setContent($request->get('answerFormContent'.$idParent));
			$comment->setParent($idParent);
			$comment->setReport(0);
		}
		else
		{
			$comment->setAuthor($request->get('commentFormPseudo' .$idParent));
			$comment->setContent($request->get('commentFormContent'.$idParent));
			$comment->setParent(null);
			$comment->setReport(0);
		}

		$app['dao.comment']->save($comment);
		$app['session']->getFlashBag()->add('success', 'Votre commentaire a été ajouté avec succès.');

		return $app->redirect($app['url_generator']->generate('billet', array('id' => $billet->getId())));
	}

	public function reportAction($id, $billetId, Application $app)
	{
		$billet = $app['dao.billet']->find($billetId);
		$app['dao.comment']->getReported($id);
		$app['session']->getFlashBag()->add('success', 'Le commentaire a bien été signalé.');
		return $app->redirect($app['url_generator']->generate('billet', array('id' => $billet->getId())));
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