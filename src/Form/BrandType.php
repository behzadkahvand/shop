<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Media\BrandImage;
use App\Form\Type\SlugType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BrandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code')
            ->add('title')
            ->add('subtitle')
            ->add('code', SlugType::class)
            ->add('metaDescription')
            ->add('image', MediaType::class, ['data_class' => BrandImage::class])
            ->add('description');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => Brand::class,
            'allow_extra_fields' => true
        ]);
    }
}
