<?php

namespace App\Form;

use App\Entity\OrderItem;
use App\Entity\ReturnReason;
use App\Entity\ReturnRequestItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReturnRequestItemType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('returnReason', EntityType::class, ['class' => ReturnReason::class, 'constraints' => new NotBlank()])
            ->add('orderItem', EntityType::class, ['class' => OrderItem::class, 'constraints' => new NotBlank()])
            ->add('quantity', NumberType::class, ['constraints' => [new NotBlank()], 'attr' => ['min' => 1]])
            ->add('isReturnable', ChoiceType::class, ['constraints' => new NotBlank(), 'choices'  => [1, 0]])
            ->add('description', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ReturnRequestItem::class]);
    }
}
