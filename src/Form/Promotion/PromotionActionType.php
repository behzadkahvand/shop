<?php

namespace App\Form\Promotion;

use App\Entity\PromotionAction;
use App\Service\Promotion\Action\ActionTypeRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class PromotionActionType extends AbstractType
{
    private ActionTypeRegistryInterface $actionTypeRegistry;

    public function __construct(ActionTypeRegistryInterface $actionTypeRegistry)
    {
        $this->actionTypeRegistry = $actionTypeRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', null, [
                'constraints' => [
                    new Choice([
                        'choices' => $this->actionTypeRegistry->getActionTypeNames(),
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

            $actionType = $this->actionTypeRegistry->get($data['type']);

            if (!$actionType) {
                return;
            }

            $form->add('configuration', $actionType->getConfigurationFormType(), [
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
            'data_class' => PromotionAction::class,
        ]);
    }
}
