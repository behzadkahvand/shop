<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\CampaignCommission;
use App\Entity\Category;
use App\Entity\Seller;
use App\Validator\NotInThePast;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CampaignCommissionType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('fee', NumberType::class, ['constraints' => new NotBlank()])
            ->add('category', EntityType::class, ['class' => Category::class, 'constraints' => new NotBlank()])
            ->add('brand', EntityType::class, ['class' => Brand::class, 'constraints' => new NotBlank()])
            ->add('seller', EntityType::class, ['class' => Seller::class, 'constraints' => new NotBlank()])
            ->add(
                'startDate',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'constraints' => [
                        new NotBlank(),
                        new NotInThePast()
                    ]
                ]
            )
            ->add(
                'endDate',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'constraints' => [
                        new NotBlank(),
                        new NotInThePast(),
                        new Callback(function (?DateTime $endDate, ExecutionContextInterface $context) {
                            $startDate = $context->getRoot()->getData()->getStartDate();
                            if ($startDate > $endDate) {
                                $context
                                    ->buildViolation('start date can not be after end date')
                                    ->addViolation();
                            }
                        })
                    ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CampaignCommission::class]);
    }
}
