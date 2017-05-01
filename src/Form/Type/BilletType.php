<?php

namespace Alaska\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BilletType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder 
			->add('title', TextType::class, array(
				'label' => 'Titre',
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