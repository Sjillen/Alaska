<?php

use Symfony\Component\HttpFoundation\Request;
use Alaska\Domain\Comment;
use Alaska\Form\Type\CommentType;

//Home page
$app->get('/', function () use ($app) {
	$billets = $app['dao.billet']->findAll();
	return $app['twig']->render('index.html.twig', array('billets' => $billets));
})->bind('home');

// Billet details with comments
$app->match('/billet/{id}', function ($id, Request $request) use ($app) {
	$billet = $app['dao.billet']->find($id);
	$commentFormView = null;
	if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
		// A user is fully authenticated : he can add comments
		$comment = New Comment();
		$comment->setBillet($billet);
		$user = $app['user'];
		$comment->setAuthor($user);
		$commentForm = $app['form.factory']->create(CommentType::class, $comment);
		$commentForm->handleRequest($request);
		if ($commentForm->isSubmitted() && $commentForm->isValid()) {
			$app['dao.comment']->save($comment);
			$app['session']->getFlashBag()->add('success', 'Votre commentaire a ete ajoute avec succes.');
		}
		$commentFormView = $commentForm->createView();
	}
	$comments = $app['dao.comment']->findAllByBillet($id);

	return $app['twig']->render('billet.html.twig', array(
		'billet' => $billet,
		'comments' => $comments,
		'commentForm' => $commentFormView));
})->bind('billet');

// Login form
$app->get('/login', function(Request $request) use ($app) {
	return $app['twig']->render('login.html.twig', array(
		'error'			=> $app['security.last_error']($request),
		'last_username'	=> $app['session']->get('_security.last_username'),
		));
})->bind('login');