<?php

namespace App\Form\Type\Customer;

use App\DTO\Customer\CustomerAddressData;
use App\Entity\City;
use App\Entity\District;
use App\Entity\Province;
use App\Form\Type\PointType;
use App\Validator\Address;
use App\Validator\Floor;
use App\Validator\Mobile;
use App\Validator\NationalNumber;
use App\Validator\PersianEnglishChars;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;

class CustomerAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('location', PointType::class)
            ->add('fullAddress', null, [
                'constraints' => [
                    new NotBlank(),
                    new Address()
                ]
            ])
            ->add('postalCode', null, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 10,
                        'max' => 10
                    ])
                ]
            ])
            ->add('number', NumberType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 0, 'max' => 100000]),
                ]
            ])
            ->add('unit', NumberType::class, [
                'constraints' => [
                    new Positive(),
                    new Range(['min' => 0, 'max' => 100000]),
                ]
            ])
            ->add('floor', null, [
                'constraints' => [
                    new NotBlank(),
                    new Floor()
                ]
            ])
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
            ->add('district', EntityType::class, [
                'class' => District::class
            ])
            ->add('isForeigner', CheckboxType::class, [
                'false_values' => [false, 'false', 0, '0', '', null],
            ]);

        if ('POST' !== $options['method'] || false === $options['my_address']) {
            $builder
                ->add('name', null, [
                    'constraints' => [new NotBlank(), new PersianEnglishChars()]
                ])
                ->add('family', null, [
                    'constraints' => [new NotBlank(), new PersianEnglishChars()]
                ])
                ->add('nationalCode', null, [
                    'constraints' => [new NotBlank(), new NationalNumber()]
                ])
                ->add('mobile', null, [
                    'constraints' => [new NotBlank(), new Mobile()]
                ]);
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $customer = $event->getData();
            $form = $event->getForm();

            if ($customer && isset($customer['isForeigner']) && true === $customer['isForeigner']) {
                $form->add('pervasiveCode', TextType::class, [
                    'constraints' => [new Length(['max' => 16])]
                ]);
                return;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => CustomerAddressData::class,
            'allow_extra_fields' => true,
            'my_address'         => false
        ]);

        $resolver->setAllowedTypes('my_address', ['bool', 'boolean']);
    }
}
