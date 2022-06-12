<?php

namespace App\Form\Type\Seller\Package;

use App\Dictionary\SellerPackageType;
use App\Dictionary\ShippingCategoryName;
use App\DTO\Seller\Package\CreateSellerPackageData;
use App\Entity\SellerOrderItem;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class CreateSellerPackageDataType
 */
final class CreateSellerPackageDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', EntityType::class, [
                'class'       => SellerOrderItem::class,
                'multiple'    => true,
                'constraints' => [
                    new Callback([
                        'callback' => function (
                            ArrayCollection $sellerOrderItems,
                            ExecutionContextInterface $context,
                            $payload
                        ) {
                            $orderShipmentTypes = [];

                            foreach ($sellerOrderItems as $sellerOrderItem) {
                                $orderShipment        = $sellerOrderItem->getOrderItem()
                                                                        ->getOrderShipment()
                                                                        ->getTitle();

                                $orderShipmentTypes[] = $orderShipment == ShippingCategoryName::FMCG ?
                                    SellerPackageType::FMCG :
                                    SellerPackageType::NON_FMCG;
                            }

                            if (count(array_unique($orderShipmentTypes)) !== 1) {
                                $context->buildViolation("All package types must be of the same type.")
                                        ->addViolation();
                            }
                        },
                    ]),
                ],
            ])
            ->add("type");
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateSellerPackageData::class,
        ]);
    }
}
