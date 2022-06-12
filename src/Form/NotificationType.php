<?php

namespace App\Form;

use App\Dictionary\NotificationSectionDictionary;
use App\Dictionary\NotificationTypeDictionary;
use App\Entity\Notification;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\ChoiceValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class NotificationType extends AbstractType
{
    private Environment $templateEngine;

    private array $editableNotifications;

    /**
     * NotificationType constructor.
     *
     * @param Environment $templateEngine
     * @param array $editableNotifications
     */
    public function __construct(Environment $templateEngine, array $editableNotifications)
    {
        $this->templateEngine = $templateEngine;
        $this->editableNotifications = $editableNotifications;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sections = array_values(NotificationSectionDictionary::toArray());
        $codes    = array_map(fn(array $section) => array_column($section, 'code'), $this->editableNotifications);

        $builder
            ->add('notificationType', ChoiceType::class, [
                'choices' => array_values(NotificationTypeDictionary::toArray()),
                'constraints' => [new NotBlank()],
            ])
            ->add('section', ChoiceType::class, [
                'choices' => $sections,
                'constraints' => [new NotBlank()],
            ])
            ->add('template', null, [
                'constraints' => [new NotBlank(), new Callback($this->getTemplateValidator())]
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($codes) {
            $data = $event->getData();
            $section = $data['section'];

            $event->getForm()->add('code', ChoiceType::class, [
                'choices' => $codes[$section],
                'constraints' => [new NotBlank()],
            ]);
        });
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Notification::class,
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * @return \Closure
     */
    private function getTemplateValidator(): \Closure
    {
        return function ($template, ExecutionContextInterface $context) {
            $form    = $context->getRoot();
            $section = $form->get('section')->getViewData();

            if (!(is_string($section) || is_numeric($section)) || !isset($this->editableNotifications[$section])) {
                return;
            }

            $code = $form->get('code')->getViewData();

            if (!is_string($code)) {
                return;
            }

            $codes = array_map(fn(array $section) => array_column($section, 'code'), $this->editableNotifications);

            if (!in_array($code, $codes[$section], true)) {
                return;
            }

            $parameters = collect($this->editableNotifications[$section])->map(
                fn(array $item) => $item['code'] === $code ? $item['variables'] : null
            )->filter()->first(null, []);

            $realLoader = $this->templateEngine->getLoader();
            $key        = md5($template);

            $this->templateEngine->setLoader(new ArrayLoader([$key => $template]));

            try {
                $this->templateEngine->render($key, $parameters);
            } catch (\Exception $e) {
                $context->buildViolation($e->getRawMessage())
                        ->atPath('template')
                        ->addViolation();
            } finally {
                $this->templateEngine->setLoader($realLoader);
            }
        };
    }
}
