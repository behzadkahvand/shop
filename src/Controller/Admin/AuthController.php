<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Admin;
use App\Response\Auth\OtpVerifyResponse;
use Gesdinet\JWTRefreshTokenBundle\Service\RefreshToken;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/auth", name: "auth.")]
class AuthController extends Controller
{
    /**
     * @OA\Tag(name="Admin Authentication")
     * @OA\Response(
     *     response=200,
     *     description="Returns currently logged in user",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Admin::class, groups={"default"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/me", name: "profile", methods: ["GET"])]
    public function me(): JsonResponse
    {
        return $this->respond($this->getUser());
    }

    /**
     * @OA\Tag(name="Admin Refresh Token")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="refresh_token", type="string")
     *     )
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="Refresh Token response",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(@OA\Property(property="access_token", type="string")),
     *            @OA\Items(@OA\Property(property="refresh_token", type="string")),
     *            @OA\Items(@OA\Property(property="token_type", type="string")),
     *            @OA\Items(@OA\Property(property="expires_in", type="integer")),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/refresh", methods: ["POST"])]
    public function refreshToken(Request $request, RefreshToken $adminRefreshToken): JsonResponse
    {
        $response = $adminRefreshToken->refresh($request);

        if ($response instanceof JWTAuthenticationSuccessResponse) {
            return $this->respond(OtpVerifyResponse::createResponse($response)->toArray());
        }

        return $response;
    }
}
