<?php

namespace App\Controller\Common;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class SecurityController extends AbstractController
{
    /**
     * @OA\Tag(name="Admin")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="username", type="string"),
     *         @OA\Property(property="password", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return token and account info",
     *     @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="token", type="string")
     *     )
     * )
     */
    #[Route("/admin/security/login", name: "admin.security.login", methods: ["POST"])]
    public function admin()
    {
    }

    /**
     * @OA\Tag(name="Seller")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="username", type="string"),
     *         @OA\Property(property="password", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return token and account info",
     *     @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="token", type="string")
     *     )
     * )
     */
    #[Route("/seller/security/login", name: "seller.security.login", methods: ["POST"])]
    public function seller()
    {
    }

    /**
     * @OA\Tag(name="Carrier")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="username", type="string"),
     *         @OA\Property(property="password", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return token and account info",
     *     @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="token", type="string")
     *     )
     * )
     */
    #[Route("/carrier/security/login", name: "carrier.security.login", methods: ["POST"])]
    public function carrier()
    {
    }
}
