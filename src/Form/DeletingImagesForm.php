<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

class DeletingImagesForm
{
    public function createForm(FormBuilderInterface $builder, array $images): FormInterface
    {
        return $builder
            ->add('images_to_delete', ChoiceType::class, [
                'label' => 'choose images to delete',
                'choices' => $images,
                'choice_label' => 'filename',
                'multiple' => true,
                'expanded' => true
            ])
            ->getForm()
        ;
    }
}
