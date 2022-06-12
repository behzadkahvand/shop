<?php

namespace App\Form;

use App\Dictionary\RateAndReviewStatus;
use App\Dictionary\RateAndReviewSuggestion;
use App\Entity\Admin;
use App\Entity\RateAndReview;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;

class RateAndReviewType extends AbstractType
{
    /**
     * @throws \ReflectionException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Title cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('body', null, [
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                    new Length([
                        'max' => 2000,
                        'maxMessage' => 'Body cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('suggestion', ChoiceType::class, [
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                ],
                'choices' => array_values(RateAndReviewSuggestion::toArray()),
            ])
            ->add('rate', null, [
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Range([
                        'min' => 1,
                        'max' => 5,
                        'invalidMessage' => "It's not a valid rate.",
                    ]),
                ],
            ])
            ->add('anonymous', CheckboxType::class, [
                'false_values' => [false, 'false', 0, '0', '', null],
            ])
            ->add('pin', CheckboxType::class, [
                'false_values' => [false, 'false', 0, '0', '', null],
            ]);

        if ($options['updatedBy'] instanceof Admin) {
            $builder->add('status', ChoiceType::class, [
                'choices' => array_values(RateAndReviewStatus::toArray()),
                'constraints' => [new NotBlank()],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RateAndReview::class,
            'updatedBy' => null,
        ]);
    }
}
