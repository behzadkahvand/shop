<?php

namespace App\Form\Type\Lendo;

use App\Validator\Mobile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

class WalletType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add(
                'userMobile',
                TextType::class,
                ['constraints' => [new NotBlank(), new Mobile()]]
            )
            ->add(
                'amount',
                NumberType::class,
                ['constraints' => [new NotBlank(), new Positive()]]
            )
            ->add(
                'referenceId',
                TextType::class,
                ['constraints' => [new NotBlank(), new NotNull()]]
            );
    }
}
