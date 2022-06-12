<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Entity\Account;
use App\Entity\Customer;
use App\Exceptions\CacheKeyNotFoundException;
use App\Form\CustomerType;
use App\Service\Account\VerifyCardServiceInterface;
use App\Service\Customer\CustomerServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route("/customers", name: "customers.")]
class CustomerController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Customer")
     * @OA\Response(
     *     response=200,
     *     description="Return customer details",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Customer::class, groups={"customer.customer.read"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "show", methods: ["GET"])]
    public function show(): JsonResponse
    {
        return $this->respond($this->getUser(), context: ['groups' => 'customer.customer.read']);
    }

    /**
     * @OA\Tag(name="Customer")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="family", type="string"),
     *         @OA\Property(property="gender", type="string"),
     *         @OA\Property(property="birthday", type="string"),
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="mobile", type="string"),
     *         @OA\Property(property="nationalNumber", type="string"),
     *         @OA\Property(property="account", type="object", ref=@Model(type=Account::class, groups={"customer.customer.read"})),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Update customer data",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Customer::class, groups={"customer.customer.read"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "update", methods: ["PATCH"])]
    public function update(Request $request, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $customer = $this->getUser();

        $form = $this->createForm(
            CustomerType::class,
            $customer,
            ['validation_groups' => 'customer.customer.update', 'method' => 'PATCH']
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($customer->getPlainPassword()) {
                $customer->setPassword($hasher->hashPassword($customer, $customer->getPlainPassword()));
                $customer->eraseCredentials();
            }
            $this->manager->persist($customer);
            $this->manager->flush();

            return $this->respond($customer, context: ['groups' => 'customer.customer.read']);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Customer")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="cardNumber", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return customer details",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Account::class, groups={"default"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/verify-card", name: "verify.card", methods: ["POST"])]
    public function verifyCard(
        Request $request,
        VerifyCardServiceInterface $verifyCardService,
        CustomerServiceInterface $customerService
    ): JsonResponse {
        try {
            $data = $verifyCardService->verify($request->request->get('cardNumber'));
        } catch (Throwable $exception) {
            return $this->respondWithError('Card number is not valid!', status: Response::HTTP_BAD_REQUEST);
        }
        $customerService->saveCardDataInCache($request->request->get('cardNumber'), $data);

        return $this->respond($data);
    }

    /**
     * @OA\Tag(name="Customer")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="cardNumber", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return customer details",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Account::class, groups={"default"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/register-card", name: "register.card", methods: ["POST"])]
    public function registerCard(Request $request, CustomerServiceInterface $customerService): JsonResponse
    {
        $cardNumber = $request->request->get('cardNumber');

        try {
            $account = $customerService->store($this->getUser(), $cardNumber);
        } catch (CacheKeyNotFoundException $exception) {
            return $this->respondWithError($exception->getMessage(), status: Response::HTTP_BAD_REQUEST);
        }

        return $this->respond($account);
    }
}
