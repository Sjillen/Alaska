<?php

namespace Alaska\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class BilletType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder 
			->add('title', TextType::class, array(
				'label' => 'Titre',
				))
			->add('pic', FileType::class, array(
				'label' => 'Image',
				))
			->add('content', TextareaType::class, array(
				'label' => 'Contenu',
				));
	}

	public function getName()
	{
		return 'billet';
	}
}