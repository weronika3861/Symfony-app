<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class MainImageForm
{
    public function createForm(FormBuilderInterface $builder, array $images): FormInterface
    {
        return $builder
            ->add('images', ChoiceType::class, [
                'label' => 'image',
                'choices' => $images,
                'choice_label' => 'filename',
                'multiple' => false,
                'expanded' => true
            ])
            ->getForm()
        ;
    }
}
