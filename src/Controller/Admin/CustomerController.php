<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Customer;
use App\Events\Auth\UserDeactivated;
use App\Form\CustomerType;
use App\Service\ORM\CustomFilters\Customer\Admin\IsLegalSearchCustomFilter;
use App\Service\ORM\CustomFilters\Customer\Admin\MultiColumnSearchCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

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
     *     description="Return list of customers",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Customer::class, groups={"default"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         MultiColumnSearchCustomFilter::class,
     *         IsLegalSearchCustomFilter::class
     *     }
     * })
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $builderFilter): JsonResponse
    {
        return $this->respondWithPagination($builderFilter->filter(Customer::class, $request->query->all()));
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
     *            ref=@Model(type=Customer::class, groups={"customer.read"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Customer $customer): JsonResponse
    {
        return $this->respond($customer, context: ['groups' => 'customer.read']);
    }

    /**
     * @OA\Tag(name="Customer")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=CustomerType::class)))
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
     *            ref=@Model(type=Customer::class, groups={"default"})
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
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(
        Request $request,
        UserPasswordHasherInterface $hasher,
        Customer $customer,
        EventDispatcherInterface $dispatcher
    ): JsonResponse {
        $customerWasActive = $customer->isActive();

        $form = $this->createForm(
            CustomerType::class,
            $customer,
            ['validation_groups' => 'customer.update', 'method' => 'PATCH']
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($customer->getPlainPassword()) {
                $customer->setPassword($hasher->hashPassword($customer, $customer->getPlainPassword()));
                $customer->eraseCredentials();
            }
            $this->manager->persist($customer);
            $this->manager->flush();

            if (!$customer->isActive() && $customerWasActive) {
                $dispatcher->dispatch(new UserDeactivated($customer));
            }

            return $this->respond($customer);
        }

        return $this->respondValidatorFailed($form);
    }
}
