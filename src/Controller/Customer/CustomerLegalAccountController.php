<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Entity\CustomerLegalAccount;
use App\Form\Type\Customer\CustomerLegalAccountType;
use App\Service\CustomerLegalAccount\CustomerLegalAccountService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/customer/legal-accounts", name: "customers.")]
class CustomerLegalAccountController extends Controller
{
    /**
     * @OA\Tag(name="Customer Legal Account")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="province", type="integer"),
     *         @OA\Property(property="city", type="integer"),
     *         @OA\Property(property="organizationName", type="string"),
     *         @OA\Property(property="economicCode", type="integer"),
     *         @OA\Property(property="nationalId", type="string"),
     *         @OA\Property(property="registrationId", type="string"),
     *         @OA\Property(property="phoneNumber", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Store Customer Legal Account",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CustomerLegalAccount::class, groups={"customer.legal.account.store"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     * @OA\Response(
     *     response=422,
     *     description="Failed validation",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean", default=false),
     *         @OA\Property(property="message", type="string", default="Validation error has been detected!"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="propertyPath", type="array", @OA\Items(type="string"))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "legal_accounts.store", methods: ["PUT"])]
    public function store(Request $request, CustomerLegalAccountService $customerLegalAccountService): JsonResponse
    {
        $form = $this->createForm(CustomerLegalAccountType::class, options: ['method' => 'PUT'])
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $customerLegalAccountData = $form->getData();
        $customerLegalAccountData->setCustomer($this->getUser());

        $customerLegalAccount = $customerLegalAccountService->store($customerLegalAccountData);

        return $this->setMessage('Customer legal account is stored successfully!')
                    ->respond($customerLegalAccount, context: ['groups' => 'customer.legal.account.store']);
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @OA\Tag(name="Customer Legal Account")
     * @OA\Response(
     *     response=200,
     *     description="Show Customer Legal Account",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CustomerLegalAccount::class, groups={"customer.legal.account.show"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "legal_accounts.show", methods: ["GET"])]
    public function show(): JsonResponse
    {
        return $this->respond($this->getUser()->getLegalAccount(), context: ['groups' => 'customer.legal.account.show']);
    }
}
