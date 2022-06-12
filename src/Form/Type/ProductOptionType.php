<?php

namespace App\Form\Type;

use App\Entity\ProductOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['only_option_values']) {
            $builder->add('values', CollectionType::class, [
                'entry_type'   => ProductOptionValueType::class,
                'allow_add'    => true,
                'by_reference' => false,
            ]);

            return;
        }

        $builder
            ->add('code')
            ->add('name');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductOption::class,
            'only_option_values' => false,
        ]);

        $resolver->setAllowedTypes('only_option_values', 'bool');
    }
}
