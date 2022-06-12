<?php

namespace App\Form;

use App\Entity\ReturnRequest;
use App\Validator\Mobile;
use App\Validator\NotInThePast;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class ReturnRequestType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {

        $builder
            ->add(
                'returnDate',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                        new NotInThePast()
                    ]
                ]
            )
            ->add(
                'customerAddress',
                TextType::class
            )
            ->add(
                'driverMobile',
                TextType::class,
                [
                    'constraints' => [new Mobile()]
                ]
            )
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $returnRequest = $event->getData();
            $form = $event->getForm();

            // checks if the ReturnRequest object is "new"
            if (!$returnRequest || null === $returnRequest->getId()) {
                $form->add(
                    'items',
                    CollectionType::class,
                    [
                        'entry_type' => ReturnRequestItemType::class,
                        'allow_add'          => true,
                        'method'             => 'POST',
                        'by_reference'  => false,
                        'constraints' => new NotBlank()
                    ]
                );
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ReturnRequest::class]);
    }
}
