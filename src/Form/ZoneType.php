<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

class ZoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $class = $options['type_class']; //ex: Province::class
        $field = $options['type_field']; //ex: provinces
        $builder
            ->add('code')
            ->add('name')
            ->add($field, EntityType::class, [
                "class" => $class,
                //hint: use this option to determine which column of the relationship to use to identify the objects
                // Id is the default option.
                //"choice_value" => "code",
                "multiple" => true,
                "constraints" => [
                    new NotBlank(['groups' => 'zone.create']),
                    new Count(['min' => 1, 'groups' => 'zone.create']),
                    new Unique(['groups' => 'zone.create']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'type_class' => null,
                'type_field' => null,
            ]
        );
    }
}
