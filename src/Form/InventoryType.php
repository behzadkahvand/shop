<?php

namespace App\Form;

use App\Dictionary\InventoryStatus;
use App\Entity\Admin;
use App\Entity\CategoryDiscountRange;
use App\Entity\Inventory;
use App\Entity\Seller;
use App\Repository\ProductVariantRepository;
use App\Service\Product\Seller\InventoryValidationConstraintsFactory;
use RuntimeException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class InventoryType extends AbstractType
{
    private ProductVariantRepository $productVariantRepository;

    private InventoryValidationConstraintsFactory $constraintsFactory;

    public function __construct(
        ProductVariantRepository $productVariantRepository,
        InventoryValidationConstraintsFactory $constraintsFactory,
        protected RequestStack $requestStack
    ) {
        $this->productVariantRepository = $productVariantRepository;
        $this->constraintsFactory       = $constraintsFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $inventory             = $options['data'];
        $variantId             = $inventory->getVariant()->getId();
        $categoryMaxLead       = $this->productVariantRepository->getCategoryLeadValueByVariantId($variantId);
        $discountRange         = $inventory->getVariant()?->getProduct()?->getCategory()?->getDiscountRange();
        $checkForCampaign      = $options['updated_by'] instanceof Seller ? $options['is_campaign_product'] : false;
        $finalPriceConstraints = $this->constraintsFactory->getFinalPriceConstraints($checkForCampaign, false);

        if ($options['updated_by'] instanceof Seller && $discountRange instanceof CategoryDiscountRange) {
            $finalPriceConstraints[] = new Callback(
                function ($finalPrice, ExecutionContextInterface $context) use ($inventory, $discountRange) {
                    $root  = $context->getRoot();
                    $price = $root->get('price')->getData() ?? $inventory->getPrice();

                    if (!$price) {
                        throw new RuntimeException('Cannot extract price from inventory or form.');
                    }

                    $price      = abs($price);
                    $finalPrice = abs($finalPrice);
                    [$finalPrice, $price] = [min($finalPrice, $price), max($finalPrice, $price)];
                    $discountPercent = 100 - ($finalPrice / $price * 100);
                    $min             = $discountRange->getMinDiscount();
                    $max             = $discountRange->getMaxDiscount();

                    if ($min <= $discountPercent && $discountPercent <= $max) {
                        return;
                    }

                    $msg = 'شما نمیتوانید کمتر از {{ min }} درصد و بیشتر از {{ max }} درصد روی این کتگوری تخفیف بدهید.';
                    $context->buildViolation($msg)
                            ->setParameters(['{{ min }}' => $min, '{{ max }}' => $max])
                            ->addViolation();
                }
            );
        }

        $stockIsChanged = true;
        if ($inventory->getSellerStock() === $this->requestStack->getCurrentRequest()->get('stock')) {
            $stockIsChanged = false;
        }

        $priceIsChanged = true;
        if ($inventory->getPrice() === $this->requestStack->getCurrentRequest()->get('price')) {
            $priceIsChanged = false;
        }

        $finalPriceIsChanged = true;
        if ($inventory->getFinalPrice() === $this->requestStack->getCurrentRequest()->get('finalPrice')) {
            $finalPriceIsChanged = false;
        }

        $builder
            ->add('stock', null, [
                'property_path' => 'sellerStock',
                'constraints'   => $stockIsChanged ? $this->constraintsFactory->getSellerStockConstraints($checkForCampaign) : [],
            ])
            ->add('price', null, [
                'constraints' => $priceIsChanged ? $this->constraintsFactory->getPriceConstraints($checkForCampaign) : [],
            ])
            ->add('finalPrice', null, [
                'constraints' => $finalPriceIsChanged ? $finalPriceConstraints : [],
            ])
            ->add('maxPurchasePerOrder', null, [
                'constraints' => $this->constraintsFactory->getMaxPurchasePerOrderConstraints(),
            ])
            ->add('sellerCode')
            ->add('isActive', CheckboxType::class, [
                'false_values' => [false, 'false', 0, '0', '', null],
            ])
            ->add('suppliesIn', null, [
                'property_path' => 'leadTime',
                'constraints'   => $this->constraintsFactory->getLeadTimeConstraints($categoryMaxLead),
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $inventory = $event->getData();

            if ($inventory->getVariant()->getProduct()->productIdentifierConstraintIsResolved()) {
                return;
            }

            $event->getForm()->addError(new FormError('برای این کالا شناسه ی کالا وارد نشده است'));
        });

        if ($options['updated_by'] instanceof Admin) {
            $builder
                ->add('status', ChoiceType::class, [
                    'choices'     => array_values(InventoryStatus::toArray()),
                    'constraints' => $this->constraintsFactory->getStatusConstraints(),
                ])
                ->add('safeTime', NumberType::class, [
                    'constraints' => [new PositiveOrZero()],
                ])
                ->add(
                    'hasCampaign',
                    CheckboxType::class,
                    [
                        'false_values' => [false, 'false', 0, '0', '', null],
                    ]
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'          => Inventory::class,
            'updated_by'          => null,
            'is_campaign_product' => null,
        ]);
    }
}
