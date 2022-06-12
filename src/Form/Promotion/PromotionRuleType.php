<?php

namespace App\Form\Promotion;

use App\Entity\PromotionRule;
use App\Service\Promotion\Rule\RuleTypeRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class PromotionRuleType extends AbstractType
{
    private RuleTypeRegistryInterface $ruleTypeRegistry;

    public function __construct(RuleTypeRegistryInterface $ruleTypeRegistry)
    {
        $this->ruleTypeRegistry = $ruleTypeRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', null, [
                'constraints' => [
                    new Choice([
                        'choices' => $this->ruleTypeRegistry->getRuleTypeNames(),
                        'groups' => 'promotion.create'
                    ])
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (!isset($data['type'])) {
                return;
            }

            $form = $event->getForm();

            $ruleType = $this->ruleTypeRegistry->get($data['type']);

            if (!$ruleType) {
                return;
            }

            $form->add('configuration', $ruleType->getConfigurationFormType(), [
                'constraints' => [
                    new Valid(['groups' => 'promotion.create']),
                    new NotBlank(['groups' => 'promotion.create']),
                ]
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PromotionRule::class,
        ]);
    }
}
