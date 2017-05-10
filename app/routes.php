<?php

use Symfony\Component\HttpFoundation\Request;
use Alaska\Domain\Comment;
use Alaska\Domain\Billet;
use Alaska\Domain\User;
use Alaska\Domain\Answer;
use Alaska\Form\Type\CommentType;
use Alaska\Form\Type\BilletType;
use Alaska\Form\Type\UserType;
use Alaska\Form\Type\AnswerType;


//Home page
$app->get('/', function () use ($app) {
	$billets = $app['dao.billet']->findAll();
	return $app['twig']->render('index.html.twig', array('billets' => $billets));
})->bind('home');

// Billet details with comments
$app->match('/billet/{id}', function ($id, Request $request) use ($app) {
	$billet = $app['dao.billet']->find($id);
	
	$answer = new Answer();
		
	
	$comments = $app['dao.comment']->findAllByBillet($id);
	
	$comment = New Comment();
	$comment->setBillet($billet);
	$answer->getAnswers()->add($comment);
	$commentForm = $app['form.factory']->create(AnswerType::class, $answer);
	$commentForm->handleRequest($request);
	if ($commentForm->isSubmitted() && $commentForm->isValid()) {
		$app['dao.comment']->save($comment);
		$app['session']->getFlashBag()->add('sucess', 'Votre commentaire a ete ajoute avec succes.');
	
	}
	$commentFormView = $commentForm->createView();

	return $app['twig']->render('billet.html.twig', array(
		'billet' => $billet,
		'comments' => $comments,	
		'commentForm' => $commentFormView,
		));
})->bind('billet');

// Login form
$app->get('/login', function(Request $request) use ($app) {
	return $app['twig']->render('login.html.twig', array(
		'error'			=> $app['security.last_error']($request),
		'last_username'	=> $app['session']->get('_security.last_username'),
		));
})->bind('login');

//Admin home page
$app->get('/admin', function() use ($app) {
	$billets = $app['dao.billet']->findAll();
	$comments = $app['dao.comment']->findAll();
	$users = $app['dao.user']->findAll();
	return $app['twig']->render('admin.html.twig', array(
		'billets' => $billets,
		'comments' => $comments,
		'users' => $users));
})->bind('admin');

// Add a new billet
$app->match('/admin/billet/add', function (Request $request) use ($app) {
	$billet = new Billet();
	$billetForm = $app['form.factory']->create(BilletType::class, $billet);
	$billetForm->handleRequest($request);
	if ($billetForm->isSubmitted() && $billetForm->isValid()) {
		$app['dao.billet']->save($billet);
		$app['session']->getFlashBag()->add('success', 'Le billet a ete cree avec succes.');
	}
	return $app['twig']->render('billet_form.html.twig', array(
		'title' => 'Nouveau billet',
		'billetForm' => $billetForm->createView()));
})->bind('admin_billet_add');

// Edit an existing billet
$app->match('/admin/billet/{id}/edit', function ($id, Request $request) use ($app) {
	$billet = $app['dao.billet']->find($id);
	$billetForm = $app['form.factory']->create(BilletType::class, $billet);
	$billetForm->handleRequest($request);
	if ($billetForm->isSubmitted() && $billetForm->isValid()) {
		$app['dao.billet']->save($billet);
		$app['session']->getFlashBag()->add('success', 'Le billet a ete modifier avec succes.');
	}
	return $app['twig']->render('billet_form.html.twig', array(
		'title' => 'Modifier billet',
		'billetForm' => $billetForm->createView()));
})->bind('admin_billet_edit');

// Remove a billet
$app->get('/admin/billet/{id}/delete', function ($id, Request $request) use ($app) {
	// Delete all associated comments
	$app['dao.comment']->deleteAllByBillet($id);
	// Delete the billet
	$app['dao.billet']->delete($id);
	$app['session']->getFlashBag()->add('success', 'Le billet a ete supprime avec succes.');
	// Redirect to admin home page
	return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_billet_delete');

//Edit an existing comment
$app->match('/admin/comment/{id}/edit', function($id, Request $request) use ($app) {
	$comment = $app['dao.comment']->find($id);
	$commentForm = $app['form.factory']->create(CommentType::class, $comment);
	$commentForm->handleRequest($request);
	if ($commentForm->isSubmitted() && $commentForm->isValid()) {
		$app['dao.comment']->save($comment);
		$app['session']->getFlashBag()->add('success', 'Le commentaire a ete modifie avec succes.');
	}
	return $app['twig']->render('comment_form.html.twig', array(
		'title' => 'Modifier commentaire',
		'commentForm' => $commentForm->createView()));
})->bind('admin_comment_edit');

//Remove a comment
$app->get('/admin/comment/{id}/delete', function($id, Request $request) use ($app) {
	// Delete the associated children
	$app['dao.comment']->deleteAllByParent($id);
	// Delete the comment
	$app['dao.comment']->delete($id);
	$app['session']->getFlashBag()->add('success', 'Le commentaire a ete supprime avec succes.');
	// Redirect to admin page
	return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_comment_delete');

// Add a user
$app->match('/admin/user/add', function(Request $request) use($app) {
	$user = new User();
	$userForm = $app['form.factory']->create(UserType::class, $user);
	$userForm->handleRequest($request);
	if ($userForm->isSubmitted() && $userForm->isValid()) {
		// Generate a random salt value
		$salt = substr(md5(time()), 0, 23);
		$user->setSalt($salt);
		$plainPassword = $user->getPassword();
		// Find the default encoder
		$encoder = $app['security.encoder.bcrypt'];
		// compute the encoded password
		$password = $encoder->encodePassword($plainPassword, $user->getSalt());
		$user->setPassword($password);
		$app['dao.user']->save($user);
		$app['session']->getFlashBag()->add('success', 'L\'utilisateur a ete cree avec succes.');
	}
	return $app['twig']->render('user_form.html.twig', array(
		'title' => 'Nouvel utilisateur',
		'userForm' => $userForm->createView()));
})->bind('admin_user_add');

//Edit an existing user
$app->match('/admin/user/{id}/edit', function($id, Request $request) use ($app) {
	$user =$app['dao.user']->find($id);
	$userForm = $app['form.factory']->create(UserType::class, $user);
	$userForm->handleRequest($request);
	if ($userForm->isSubmitted() && $userForm->isValid()) {
		$plainPassword = $user->getPassword();
		// find the encoded password
		$encoder = $app['security.encoder_factory']->getEncoder($user);
		// compute the encode password
		$password = $encoder->encodePassword($plainPassword, $user->getSalt());
		$user->setPassword($password);
		$app['dao.user']->save($user);
		$app['session']->getFlashBag()->add('success', 'L\'utilisateur a ete modifie avec succes');
	}
	return $app['twig']->render('user_form.html.twig', array(
		'title' => 'Modifier utilisateur',
		'userForm' => $userForm->createView()));
})->bind('admin_user_edit');

//Remove a user
$app->get('/admin/user/{id}/delete', function($id, Request $request) use ($app) {
	// Delete all associated comments
	$app['dao.comment']->deleteAllByUser($id);
	// Delete the user
	$app['dao.user']->delete($id);
	$app['session']->getFlashBag()->add('success', 'L\'utilisateur a ete supprime avec succes,');
	//Redirect to admin home page
	return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_user_delete');