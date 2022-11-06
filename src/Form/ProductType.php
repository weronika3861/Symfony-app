<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [ new NotBlank() ],
                'attr' => [
                    'placeholder' => 'Must have at least 5 characters',
                ]
            ])
            ->add('description')
            ->add('categories', EntityType::class, [
                'label_attr' => [
                    'class' => 'checkbox-custom',
                ],
                'class' => ProductCategory::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true
            ])
            ->add('images', FileType::class, [
                'label' => "images",
                'multiple' => true,
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '1024k',
                                'mimeTypes' => [ "image/*" ]
                            ])
                        ]
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class
        ]);
    }
}
