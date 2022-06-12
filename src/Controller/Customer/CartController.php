<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Cart;
use App\Entity\CustomerAddress;
use App\Entity\Inventory;
use App\Form\CartCouponType;
use App\Repository\CustomerAddressRepository;
use App\Service\Cart\CartService;
use App\Service\Cart\CartStorageService;
use App\Service\Cart\Exceptions\CartNotFoundException;
use App\Service\Configuration\ConfigurationService;
use App\Service\PartialShipment\PartialShipmentService;
use App\Service\PartialShipment\Types\AbstractPartialShipment;
use App\Service\PartialShipment\ValueObject\ExpressPartialDeliveryDate;
use App\Service\PartialShipment\ValueObject\PartialShipmentItem;
use App\Service\Promotion\Action\PromotionApplicator;
use App\Validator\CustomerAddress as CustomerAddressConstraint;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/cart", name: "cart.")]
class CartController extends Controller
{
    public function __construct(private CartService $cartService, private ConfigurationService $configurationService)
    {
    }

    /**
     * @OA\Tag(name="Cart")
     * @OA\Response(
     *     response=200,
     *     description="Add an item to Shopping Cart",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *             property="results",
     *             type="object",
     *             ref=@Model(type=Cart::class, groups={"cart.show"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "add", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function add(Inventory $inventory): JsonResponse
    {
        $cart = $this->cartService->addToCart($inventory);

        return $this->respond($cart, context: ['groups' => 'cart.show']);
    }

    /**
     * @OA\Tag(name="Cart")
     * @OA\Response(
     *     response=200,
     *     description="Remove an item from Shopping Cart",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *             property="results",
     *             type="object",
     *             ref=@Model(type=Cart::class, groups={"cart.show"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "remove", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function remove(Inventory $inventory): JsonResponse
    {
        $cart = $this->cartService->remove($inventory);

        return $this->respond($cart, context: ['groups' => 'cart.show']);
    }

    /**
     * @OA\Tag(name="Cart")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="quantity", type="integer"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Change an item quantity in Shopping Cart",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *             property="results",
     *             type="object",
     *             ref=@Model(type=Cart::class, groups={"cart.show"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "change", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function change(Inventory $inventory, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $quantity = $request->request->get('quantity');

        $violations = $validator->validate(compact('quantity'), new Collection([
            'fields' => [
                'quantity' => [new Positive(), new NotBlank(), new Type('int')],
            ],
        ]));

        if (count($violations) > 0) {
            return $this->respondValidationViolation($violations);
        }

        $cart = $this->cartService->change($inventory, $quantity);

        return $this->respond($cart, context: ['groups' => 'cart.show']);
    }

    /**
     * @OA\Tag(name="Cart")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="promotionCoupon", type="string"),
     *         @OA\Property(property="address", type="integer")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Coupon applied to your cart.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *             property="results",
     *             type="object",
     *             ref=@Model(type=Cart::class, groups={"cart.show"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     * @OA\Response(
     *     response=400,
     *     description="Coupon is not valid.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean", default=false),
     *         @OA\Property(property="message", type="string", default="Coupon is not valid."),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="propertyPath", type="array", @OA\Items(type="string"))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/coupon", name: "coupon", methods: ["PATCH"])]
    public function coupon(Request $request, CartService $cartService, CartStorageService $cartStorage): JsonResponse
    {
        try {
            $cart = $cartStorage->findCartOrFail();
        } catch (CartNotFoundException $exception) {
            return $this->respondWithError($exception->getMessage());
        }

        $form = $this->createForm(CartCouponType::class, $cart);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $cartService->save();

            return $this->respond($cart, context: ['groups' => 'cart.show']);
        }

        return $this->respondValidatorFailed($form, false);
    }

    /**
     * @OA\Tag(name="Cart")
     * @OA\Response(
     *     response=200,
     *     description="Coupon deleted from your cart.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *             property="results",
     *             type="object",
     *             ref=@Model(type=Cart::class, groups={"cart.show"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/coupon", name: "coupon.delete", methods: ["DELETE"])]
    public function clearCoupon(CartStorageService $cartStorage, PromotionApplicator $promotionApplicator): JsonResponse
    {
        try {
            $cart = $cartStorage->findCartOrFail();
            $promotionCoupon = $cart->getPromotionCoupon();
            if (null !== $promotionCoupon) {
                $promotionApplicator->revert($cart, $promotionCoupon->getPromotion());
                $cart->setPromotionCoupon(null);

                $this->cartService->save();
            }

            return $this->respond($cart, context: ['groups' => 'cart.show']);
        } catch (CartNotFoundException $exception) {
            return $this->respondWithError($exception->getMessage());
        }
    }

    /**
     * @OA\Tag(name="Cart")
     * @OA\Response(
     *     response=200,
     *     description="Calculate shpments based on Cart Items",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *             property="results",
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="subTotal", type="integer"),
     *             @OA\Property(property="grandTotal", type="integer"),
     *             @OA\Property(property="messages", type="array", @OA\Items(type="string")),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="shippingMethod", type="string"),
     *                     @OA\Property(property="price", type="integer"),
     *                     @OA\Property(
     *                         property="items",
     *                         type="array",
     *                         @OA\Items(ref=@Model(type=PartialShipmentItem::class, groups={"cart.show",
     *     "cart.shipments", "promotionCoupon.read", "promotionDiscount.read"}))
     *                     ),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(
     *                         property="deliveryDates",
     *                         type="array",
     *                         @OA\Items(
     *                            ref=@Model(type=ExpressPartialDeliveryDate::class,
     *                            groups={"cart.show", "cart.shipments", "promotionCoupon.read", "promotionDiscount.read"})
     *                         )
     *                     ),
     *                 ),
     *             )
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "show", methods: ["GET"])]
    public function show(
        Request $request,
        PartialShipmentService $shipmentGroup,
        CustomerAddressRepository $addressRepository
    ): JsonResponse {
        if ($request->query->has('address')) {
            $form = $this->createFormBuilder(options: ['method' => 'GET', 'allow_extra_fields' => true])
                         ->add('address', EntityType::class, [
                             'class'       => CustomerAddress::class,
                             'constraints' => [new NotBlank(), new CustomerAddressConstraint()],
                         ])
                         ->getForm();

            $form->submit($request->query->all());

            if (!$form->isValid()) {
                return $this->respondValidatorFailed($form);
            }

            $address = $form['address']->getData();
        } else {
            $address = $addressRepository->findOneInTehran();

            if ($user = $this->getUser()) {
                $customerAddresses = $user->getAddresses()->toArray();
                $callback          = fn(CustomerAddress $address) => $address->getIsDefault();
                $address           = collect($customerAddresses)->first($callback, $address);
            }
        }

        $cart              = $this->cartService->view(['address' => $address]);
        $isExpressDelivery = $address ? $address->getCity()->isExpress() : true;
        $shipments         = $shipmentGroup->createFromCart($cart, $address, $isExpressDelivery);
        $shipmentTotal     = collect($shipments)->sum(function (AbstractPartialShipment $shipment) {
            return $shipment->getPrice()->getGrandTotal();
        });

        $result = [
            'id'                => $cart->getId(),
            'subTotal'          => $cart->getSubtotal(),
            'grandTotal'        => $cart->getGrandTotal(),
            'messages'          => $cart->getMessages(),
            'shipments'         => $shipments,
            'shipmentsTotal'    => $shipmentTotal,
            'total'             => $cart->getSubtotal() - $cart->getGrandTotal() + $shipmentTotal,
            'minimumCart'       => $this->getMinimumCart(),
            'isPossibleToOrder' => $this->getMinimumCart() <= $cart->getItemsGrandTotal(),
            'itemsCount'        => $cart->getItemsCount(),
            'coupon'            => $cart->getPromotionCoupon(),
            'discounts'         => $cart->getDiscounts(),
        ];

        return $this->respond(
            $result,
            context: [
                'groups'          => ['cart.shipments', 'cart.show', 'promotionCoupon.read', 'promotionDiscount.read'],
                'datetime_format' => 'Y-m-d',
            ]
        );
    }

    private function getMinimumCart(): ?int
    {
        $configuration = $this->configurationService->findByCode(ConfigurationCodeDictionary::MINIMUM_CART);

        $value = $configuration?->getValue();
        return $value ?: 0;
    }
}
