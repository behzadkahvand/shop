<?php

namespace App\Form;

use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class ConfigurationType extends AbstractType
{
    private EntityManagerInterface $manager;

    public function __construct(
        EntityManagerInterface $manager
    ) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', null, [
                'constraints' => [
                    new Choice(array_values(ConfigurationCodeDictionary::toArray())),
                ],
            ])
            ->add('value');

        $builder->get('value')->addModelTransformer(new CallbackTransformer(
            fn($v) => $v,
            function ($v) {
                try {
                    return json_decode($v, true, 512, JSON_THROW_ON_ERROR);
                } catch (\Exception $e) {
                    $e = new TransformationFailedException();
                    $e->setInvalidMessage('Invalid JSON string given.');

                    throw $e;
                }
            }
        ));

        $builder->addModelTransformer(new CallbackTransformer(function ($data) {
            // transformation not required
            return $data;
        }, function (Configuration $configuration) {
            // reverse transform
            $entity = $this
                    ->manager
                    ->getRepository(Configuration::class)
                    ->findOneBy(['code' => $configuration->getCode()]) ??
                (new Configuration())->setCode($configuration->getCode());

            return $entity->setValue($configuration->getValue());
        }));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => Configuration::class,
                'allow_extra_fields' => true,
            ]
        );
    }
}
