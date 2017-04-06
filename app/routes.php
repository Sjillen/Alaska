<?php

//Home page
$app->get('/', function () use ($app) {
	$billets = $app['dao.billet']->findAll();

	ob_start();				//start buffering html output
	require '../views/view.php';
	$view = ob_get_clean(); //assign html output to $view
	return $view;
});