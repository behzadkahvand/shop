<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\ShippingMethodPrice;
use App\Repository\ShippingMethodPriceRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/shipping-method-prices", name: "shipping_method_prices.")]
class ShippingMethodPriceController extends Controller
{
    /**
     * @OA\Tag(name="Shipping Method Price")
     * @OA\Response(
     *     response=200,
     *     description="Return list of shipping method prices",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ShippingMethodPrice::class, groups={"shipping-method-prices.index"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(ShippingMethodPriceRepository $shippingMethodPriceRepository): JsonResponse
    {
        return $this->respond(
            $shippingMethodPriceRepository->findAll(),
            context: ['groups' => 'shipping-method-prices.index']
        );
    }
}
