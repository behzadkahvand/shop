<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Response\Auth\OtpVerifyResponse;
use App\Response\Presenter\AuthMePresenter;
use App\Service\Auth\AuthService;
use App\Service\OTP\OtpService;
use App\Validator\Mobile;
use Gesdinet\JWTRefreshTokenBundle\Service\RefreshToken;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/auth", name: "auth.")]
class AuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected AuthService $authService,
        protected ValidatorInterface $validator
    ) {
    }

    /**
     * @OA\Tag(name="Customer Authentication")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="mobile", type="string"),
     *     )
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="OTP Sent",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="expirationTimeStamp", type="integer")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/otp/send", name: "otp.send", methods: ["POST"])]
    public function sendOtp(Request $request): JsonResponse
    {
        $violations = $this->validateSendOtp($request);

        if (count($violations) !== 0) {
            return $this->respondValidationViolation($violations);
        }

        $expirationTimeStamp = $this->otpService->sendOTP($request->get('mobile'));

        return $this->respond(['expirationTimeStamp' => $expirationTimeStamp]);
    }

    /**
     * @OA\Tag(name="Customer Authentication")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="mobile", type="string"),
     *         @OA\Property(property="code", type="string")
     *     )
     * )
     *
     * @OA\Response(
     *     response=200,
     *     description="OTP Verify",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="token", type="string"),
     *            @OA\Property(property="refreshToken", type="string"),
     *            @OA\Property(property="refreshTokenTtl", type="integer"),
     *            @OA\Property(property="tokenType", type="string"),
     *            @OA\Property(property="expireDate", type="integer"),
     *            @OA\Property(
     *                property="account",
     *                type="object",
     *                @OA\Property(property="id", type="integer"),
     *                @OA\Property(property="name", type="string"),
     *                @OA\Property(property="family", type="string"),
     *                @OA\Property(
     *                    property="address",
     *                    type="array",
     *                    @OA\Items(ref=@Model(type=CustomerAddress::class, groups={"default"}))
     *                ),
     *                @OA\Property(property="isProfileCompleted", type="boolean")
     *            ),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/otp/verify", name: "otp.verify", methods: ["POST"])]
    public function verifyOtp(Request $request): JsonResponse
    {
        $violations = $this->validateVerifyOtp($request);

        if (count($violations) !== 0) {
            return $this->respondValidationViolation($violations);
        }

        return $this->respond(
            $this->authService->loginByOtp($request->get('mobile'), $request->get('code'))->toArray()
        );
    }

    /**
     * @OA\Tag(name="Customer Authentication")
     * @OA\Response(
     *     response=200,
     *     description="Return user details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Customer::class, groups={"customer.auth.profile"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/me", name: "profile", methods: ["GET"])]
    public function me(): JsonResponse
    {
        return $this->respond(
            new AuthMePresenter($this->getUser()),
            context: ['groups' => 'customer.auth.profile']
        );
    }

    /**
     * @OA\Tag(name="Customer Refresh Token")
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
     *            type="object",
     *            @OA\Property(property="access_token", type="string"),
     *            @OA\Property(property="refresh_token", type="string"),
     *            @OA\Property(property="token_type", type="string"),
     *            @OA\Property(property="expires_in", type="integer")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/refresh", methods: ["POST"])]
    public function refreshToken(Request $request, RefreshToken $customerRefreshToken): JsonResponse
    {
        $response = $customerRefreshToken->refresh($request);

        if ($response instanceof JWTAuthenticationSuccessResponse) {
            return $this->respond(OtpVerifyResponse::createResponse($response)->toArray());
        }

        return $response;
    }

    private function validateSendOtp(Request $request): ConstraintViolationListInterface
    {
        return $this->validator->validate($request->request->all(), new Collection([
            'fields' => [
                'mobile' => [new Sequentially([new NotBlank(), new NotNull(), new Type('string'), new Mobile()])],
            ],
        ]));
    }

    private function validateVerifyOtp(Request $request): ConstraintViolationListInterface
    {
        return $this->validator->validate($request->request->all(), new Collection([
            'fields' => [
                'mobile' => [new Sequentially([new NotBlank(), new NotNull(), new Type('string'), new Mobile()])],
                'code'   => [new NotBlank(), new NotNull(), new Type('string')],
            ],
        ]));
    }
}
