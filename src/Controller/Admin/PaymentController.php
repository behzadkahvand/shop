<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Service\Payment\PaymentService;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/payments", name: "payments.")]
class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    /**
     * @OA\Tag(name="Payment")
     * @OA\Response(
     *     response=200,
     *     description="Return gateways list",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(type="string")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/gateways/online", name: "gateways.list", methods: ["GET"])]
    public function onlineGateways(): JsonResponse
    {
        return $this->respond($this->paymentService->getOnlineGateways());
    }
}
