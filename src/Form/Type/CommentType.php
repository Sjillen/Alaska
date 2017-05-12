<?php

namespace Alaska\Form\Type;

use Alaska\Domain\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


class CommentType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('author', TextType::class,array(
				'label' => 'Auteur',
				))
			->add('content', TextareaType::class, array(
				'label' => 'Contenu',
				))
			->add('parent', HiddenType::class, array(
				));
	}

	public function getName()
	{
		return 'comment';
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => Comment::class,
		));
	}

}