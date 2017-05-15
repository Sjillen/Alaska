<?php

// Home page
$app->get('/', "Alaska\Controller\HomeController::indexAction")
->bind('home');

// Detailed info about an billet
$app->match('/billet/{id}', "Alaska\Controller\HomeController::billetAction")
->bind('billet');

$app->match('/comment/{idParent}/{billetId}/add/', "Alaska\Controller\HomeController::addCommentAction")
->bind('comment_add');

$app->match('/comment/{id}/report/', "Alaska\Controller\HomeController::reportAction")
->bind('comment_report');

// Login form
$app->get('/login', "Alaska\Controller\HomeController::loginAction")
->bind('login');

// Admin zone
$app->get('/admin', "Alaska\Controller\AdminController::indexAction")
->bind('admin');

// Add a new billet
$app->match('/admin/billet/add', "Alaska\Controller\AdminController::addBilletAction")
->bind('admin_billet_add');

// Edit an existing billet
$app->match('/admin/billet/{id}/edit', "Alaska\Controller\AdminController::editBilletAction")
->bind('admin_billet_edit');

// Remove an billet
$app->get('/admin/billet/{id}/delete', "Alaska\Controller\AdminController::deleteBilletAction")
->bind('admin_billet_delete');

// Edit an existing comment
$app->match('/admin/comment/{id}/edit', "Alaska\Controller\AdminController::editCommentAction")
->bind('admin_comment_edit');

// Remove a comment
$app->get('/admin/comment/{id}/delete', "Alaska\Controller\AdminController::deleteCommentAction")
->bind('admin_comment_delete');

// Add a user
$app->match('/admin/user/add', "Alaska\Controller\AdminController::addUserAction")
->bind('admin_user_add');

// Edit an existing user
$app->match('/admin/user/{id}/edit', "Alaska\Controller\AdminController::editUserAction")
->bind('admin_user_edit');

// Remove a user
$app->get('/admin/user/{id}/delete', "Alaska\Controller\AdminController::deleteUserAction")
->bind('admin_user_delete');

