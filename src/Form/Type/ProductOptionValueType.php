<?php

namespace App\Form\Type;

use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class)
            ->add('value', TextType::class)
            ->add(
                'attributes',
                CollectionType::class,
                ['prototype' => true, 'allow_add' => true, 'allow_extra_fields' => true]
            );

        if ('POST' === $options['method']) {
            $builder->add('option', EntityType::class, [
                'class' => ProductOption::class,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => ProductOptionValue::class]);
    }
}
