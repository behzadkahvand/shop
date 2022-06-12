<?php

namespace App\Form;

use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Validator\PersianEnglishChars;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('name', null, [
                'constraints' => [
                    new NotBlank(),
                    new PersianEnglishChars()
                ]
            ])
            ->add('password', TextType::class, ['property_path' => 'plainPassword'])
            ->add('family', null, [
                'constraints' => [
                    new NotBlank(),
                    new PersianEnglishChars()
                ]
            ])
            ->add('status')
            ->add('username')
            ->add('gender')
            ->add('isForeigner', CheckboxType::class, [
                'false_values' => [false, 'false', 0, '0', '', null],
            ])
            ->add(
                'birthday',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                ]
            )
            ->add(
                'addresses',
                CollectionType::class,
                [
                    'entry_type'    => CustomerAddressType::class,
                    'entry_options' => ['label' => false],
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false,
                ]
            )
            ->add('account', AccountType::class)
            ->add('isActive');

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $customer = $event->getData();
            $form = $event->getForm();

            if ($customer && isset($customer['isForeigner']) && true === $customer['isForeigner']) {
                $form->add('nationalNumber', TextType::class);
                $form->add('pervasiveCode', TextType::class, [
                    'constraints' => [new Length(['max' => 16])]
                ]);
                return;
            }

            $form->add('nationalNumber', TextType::class, [
                'constraints' => [
                    new NotBlank(['groups' => ["customer.update", "customer.customer.update", "order.store"]])
                ],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => Customer::class,
                'allow_extra_fields' => true,
            ]
        );
    }
}
