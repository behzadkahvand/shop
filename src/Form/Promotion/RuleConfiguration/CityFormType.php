<?php

namespace App\Form\Promotion\RuleConfiguration;

use App\Entity\City;
use App\Entity\Product;
use App\Repository\CityRepository;
use App\Service\Promotion\Rule\CityRuleType;
use App\Service\Promotion\Rule\ProductRuleType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CityFormType extends AbstractType
{
    private CityRepository $cityRepository;

    public function __construct(CityRepository $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(CityRuleType::CONFIGURATION_CITY_IDS, CollectionType::class, [
                'constraints' => [
                    new NotBlank(['groups' => 'promotion.create']),
                ],
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => City::class,
                ],
                'allow_add' => true,
                'allow_delete' => false,
                'by_reference' => false,
                'error_bubbling' => false,
            ])
        ;

        $builder->get(CityRuleType::CONFIGURATION_CITY_IDS)->addViewTransformer(new CallbackTransformer(
            function ($collection) {
                if ($collection === null || empty($collection)) {
                    return $collection;
                }

                return $this->cityRepository->findBy(['id' => $collection]);
            },
            function ($collection) {
                if ($collection === null) {
                    return null;
                }

                return array_map(fn($entity) => $entity->getId(), $collection);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
