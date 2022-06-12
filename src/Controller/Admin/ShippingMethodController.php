<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\ShippingMethod;
use App\Repository\ShippingMethodRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/shipping-methods", name: "shipping_methods.")]
class ShippingMethodController extends Controller
{
    /**
     * @OA\Tag(name="Shipping Method")
     * @OA\Response(
     *     response=200,
     *     description="Return list of shipping methods",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=ShippingMethod::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(ShippingMethodRepository $shippingMethodRepository): JsonResponse
    {
        return $this->respond($shippingMethodRepository->findAll());
    }

    /**
     * @OA\Tag(name="Shipping Method")
     * @OA\Response(
     *     response=200,
     *     description="Return shipping method details",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ShippingMethod::class, groups={"shipping-methods.grid"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(ShippingMethod $shippingMethod): JsonResponse
    {
        return $this->respond($shippingMethod, context: ['groups' => ["shipping-methods.grid"]]);
    }
}
