<?php

namespace App\Form;

use App\Entity\Cart;
use App\Entity\CustomerAddress;
use App\Entity\PromotionCoupon;
use App\Repository\PromotionCouponRepository;
use App\Validator\CustomerAddress as CustomerAddressConstraint;
use App\Validator\Promotion\PromotionSubjectCoupon;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CartCouponType extends AbstractType
{
    private PromotionCouponRepository $repository;

    public function __construct(PromotionCouponRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('promotionCoupon', TextType::class, [
                'constraints' => [new NotBlank(['message' => 'این کد تخفیف معتبر نمی باشد. '])],
            ])
            ->add('address', EntityType::class, [
                'class' => CustomerAddress::class,
                'constraints' => [new CustomerAddressConstraint()],
            ])
        ;

        $builder->get('promotionCoupon')->addViewTransformer(new CallbackTransformer(
            function ($coupon) {
                if (null === $coupon) {
                    return '';
                }

                if (!$coupon instanceof PromotionCoupon) {
                    throw new UnexpectedTypeException($coupon, PromotionCoupon::class);
                }

                return $coupon->getCode();
            },
            function ($code) {
                if (null === $code || '' === $code) {
                    return null;
                }

                return $this->repository->findOneBy(['code' => strtolower($code)]);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cart::class,
            'constraints' => [
                new PromotionSubjectCoupon(),
            ]
        ]);
    }
}
