<?php

namespace App\Form\Type\Customer;

use App\DTO\Customer\CustomerLegalAccountData;
use App\Entity\City;
use App\Entity\Province;
use App\Validator\Address;
use App\Validator\Floor;
use App\Validator\PersianEnglishChars;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;

class CustomerLegalAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('province', EntityType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'class'       => Province::class
            ])
            ->add('city', EntityType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'class'       => City::class
            ])
            ->add('organizationName', null, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 10
                    ])
                ]
            ])
            ->add('economicCode', NumberType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Range([
                        'min' => 100_000_000_000,
                        'max' => 9_999_999_999_999_999,
                    ])
                ]
            ])
            ->add('nationalId', null, [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('registrationId', null, [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('phoneNumber', null, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 8,
                        'max' => 11,
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomerLegalAccountData::class,
        ]);
    }
}
