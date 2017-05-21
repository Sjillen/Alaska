<?php

namespace Alaska\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Alaska\Domain\Billet;
use Alaska\Domain\User;
use Alaska\Form\Type\BilletType;
use Alaska\Form\Type\CommentType;
use Alaska\Form\Type\UserType;

class AdminController 
{
	/**
	 * Admin home page controller.
	 *
	 * @param Application $app Silex application
	 */
	public function indexAction(Application $app) 
	{	
		$billets 	= $app['dao.billet']->findAll();
		$comments 	= $app['dao.comment']->findAll();
		$users 		= $app['dao.user']->findAll();

		return $app['twig']->render('admin.html.twig', array(
			'billets' 	=> $billets,
			'comments'	=> $comments,
			'users'		=> $users));
	}

	/**
	 * Add billet controller.
	 *
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */
	public function addBilletAction(Request $request, Application $app)
	{
		$billets = $app['dao.billet']->findAll();
		$billet = new Billet();
		$billetForm = $app['form.factory']->create(BilletType::class, $billet);
		$billetForm->handleRequest($request);
		if ($billetForm->isSubmitted() && $billetForm->isValid()) 
		{
			if ($billet->getImg() == null)
			{
				$billet->setImg(null);
			}
			else
			{
				$billet = $this->setImgHeader($billet, $app);
			}
			$app['dao.billet']->save($billet);
			$app['session']->getFlashBag()->add('success', 'Le billet a été ajouté avec succès.');
		}
		return $app['twig']->render('billet_form.html.twig', array(
			'billets'		=> $billets,
			'title' 		=> 'New billet',
			'billetForm' 	=> $billetForm->createView()));

	}

	/**
	 * Edit billet controller.
	 *
	 * @param integer $id Billet id
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */
	public function editBilletAction($id, Request $request, Application $app)
	{
		$billets = $app['dao.billet']->findAll();
		$billet = $app['dao.billet']->find($id);
		$billetImg = $billet->getImg();
		$billetForm = $app['form.factory']->create(BilletType::class, $billet);
		$billetForm->handleRequest($request);
		if ($billetForm->isSubmitted() && $billetForm->isValid())
		{
			if ($billetImg !== 'assets/images/default.jpg')
			{
				if ($billet->getImg() !== null)
				{
					$billet = $this->setImgHeader($billet, $app);
				}
				else
				{
					$billet->setImg($billetImg);
				}
			}
			elseif ($billet->getImg() !== null)
			{
				$billet = $this->setImgHeader($billet, $app);
			}
			else
			{
				$billet->setImg($billetImg);
			}
			$app['dao.billet']->save($billet);
			$app['session']->getFlashBag()->add('success', 'Le billet a été modifié avec succès.');
		}
		return $app['twig']->render('billet_form.html.twig', array(
			'billets'		=> $billets,
			'title'			=> 'Edit billet',
			'billetForm'	=> $billetForm->createView()));
	}

	/**
	 * Delete billet controller.
	 *
	 * @param integer $id Billet id
	 * @param Application $app Silex application
	 */
	public function deleteBilletAction($id, application $app)
	{
		// Delete all associated comments
		$app['dao.comment']->deleteAllByBillet($id);
		// Delete the billet
		$app['dao.billet']->delete($id);
		$app['session']->getFlashBag()->add('success', 'Le billet a été supprimé avec succès.');
		// Redirect to admin home page
		return $app->redirect($app['url_generator']->generate('admin'));
	}

	private function setImgHeader (Billet $billet, Application $app)
	{
		$img = $billet->getImg();
		$messageUser = $app['dao.file']->uploadable($img, array('jpeg', 'png'));
		if ($messageUser !== true)
		{
			$app['session']->getFlashBag()->add($messageUser[0], $messageUser[1]);
			$billet->setImg('assets/images/default.jpg');
		}
		else
		{
			$newWidth = 750;
			$maxHeight = 700;
			$messageUser = $app['dao.file']->checkImageDimension($img, $newWidth, $maxHeight);
			if (array_key_exists('newHeight', $messageUser))
			{
				$filename = $app['dao.file']->uploadFile($img, 'assets/images/', $newWidth, $messageUser['newHeight']);
				if ($filename) 
				{
					$billet->setImg('assets/images/' . $filename);
					return $billet;
				}
			}
			else
			{
				$app['session']->getFlashBag()->add($messageUser[0], $messageUser[1]);
				$billet->setImg('assets/images/default.jpg');
			}
		}
		return $billet;
	}

	/**
	 * Edit comment controller.
	 *
	 * @param integer $id Comment id
	 * @param Reqeust $request Incoming request
	 * @param Application $app Silex application
	 */
	public function editCommentAction($id, Request $request, Application $app)
	{
		$billets = $app['dao.billet']->findAll();
		$comment = $app['dao.comment']->find($id);
		$commentForm = $app['form.factory']->create(CommentType::class, $comment);
		$commentForm->handleRequest($request);
		if ($commentForm->isSubmitted() && $commentForm->isValid())
		{
			$app['dao.comment']->save($comment);
			$app['session']->getFlashBag()->add('sucess', 'Le commentaire a été modifié avec succès.');
		}
		return $app['twig']->render('comment_form.html.twig', array(
			'billets'		=> $billets,
			'title'			=> 'Edit comment',
			'commentForm'	=> $commentForm->createView()));
	}

	/**
	 * Delete comment controller.
	 *
	 * @param integer $id Comment id
	 * @param Application $app Silex application
	 */
	public function deleteCommentAction($id, Application $app)
	{
		// Delete all associated children comments
		$comment = $app['dao.comment']->deleteAllByParent($id);
		// Delete the comment
		$comment = $app['dao.comment']->delete($id);
		$app['session']->getFlashBag()->add('success', 'Le commentaire et les commentaires associés ont été supprimés avec succès.');
		// Redirect to admin home page
		return $app->redirect($app['url_generator']->generate('admin'));
	}

	/**
	 * Add user controller.
	 *
	 * @param Request $request Incoming request
	 * @param Application $app Silex application
	 */
	public function addUserAction (Request $request, Application $app)
	{
		$billets = $app['dao.billet']->findAll();
		$user = new User();
		$userForm = $app['form.factory']->create(UserType::class, $user);
		$userForm->handleRequest($request);
		if ($userForm->isSubmitted() && $userForm->isValid())
		{
			// generate a random  salt value
			$salt = substr(md5(time()), 0, 23);
			$user->setSalt($salt);
			$plainPassword = $user->getPassword();
			// find the default encoder
			$encoder = $app['security.encoder.bcrypt'];
			// compute the encoded password
			$password = $encoder->encodePassword($plainPassword, $user->getSalt());
			$user->setPassword($password);
			$app['dao.user']->save($user);
			$app['session']->getFlashBag()->add('success', 'Le profil utilisateur a été créé avec succès.');
		}
		return $app['twig']->render('user_form.html.twig', array(
			'billets'		=> $billets,
			'title'			=> 'New User',
			'userForm'		=> $userForm->createView()));
	}

	public function editUserAction ($id, Request $request, Application $app)
	{
	 	$billets = $app['dao.billet']->findAll();
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
	 		'billets' => $billets,
	 		'title' => 'Modifier utilisateur',
	 		'userForm' => $userForm->createView()));
 	}

    /**
     * Delete user controller.
     *
     * @param integer $id User id
     * @param Application $app Silex application
     */
    public function deleteUserAction($id, Application $app) {
        // Delete the user
        $app['dao.user']->delete($id);
        $app['session']->getFlashBag()->add('success', 'Le profil utilisateur a été supprimé avec succès.');
        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin'));
    }

}