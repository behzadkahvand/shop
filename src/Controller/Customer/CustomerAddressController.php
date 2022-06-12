<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Entity\CustomerAddress;
use App\Form\Type\Customer\CustomerAddressType;
use App\Repository\CustomerAddressRepository;
use App\Repository\CustomerRepository;
use App\Service\CustomerAddress\CreateCustomerAddressService;
use App\Service\CustomerAddress\UpdateCustomerAddressService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/customer-addresses", name="customer_addresses.")
 */
class CustomerAddressController extends Controller
{
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerAddressRepository $customerAddressRepository,
        protected EntityManagerInterface $manager
    ) {
    }

    /**
     * @OA\Tag(name="Customer Address")
     * @OA\Response(
     *     response=200,
     *     description="Return list of customer addresses",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=CustomerAddress::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(): JsonResponse
    {
        return $this->respond($this->customerAddressRepository->findBy(['customer' => $this->getUser()]));
    }

    /**
     * @OA\Tag(name="Customer Address")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *         property="location",
     *         type="object",
     *         @OA\Property(property="lat", type="integer"),
     *         @OA\Property(property="long", type="integer")),
     *         @OA\Property(property="fullAddress", type="string"),
     *         @OA\Property(property="postalCode", type="integer"),
     *         @OA\Property(property="number", type="integer"),
     *         @OA\Property(property="province", type="integer"),
     *         @OA\Property(property="city", type="integer"),
     *         @OA\Property(property="district", type="integer"),
     *         @OA\Property(property="unit", type="integer"),
     *         @OA\Property(property="floor", type="integer"),
     *         @OA\Property(property="myAddress", type="boolean"),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="family", type="string"),
     *         @OA\Property(property="nationalCode", type="string"),
     *         @OA\Property(property="mobile", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Create Customer Address",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Customer address is added successfully!"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CustomerAddress::class, groups={"default"})
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
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request, CreateCustomerAddressService $createCustomerAddress): JsonResponse
    {
        $form = $this->createForm(
            CustomerAddressType::class,
            options: [
                'my_address' => $request->request->getBoolean('myAddress', false),
                'method'     => 'POST'
            ]
        )->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $customerAddressData = $form->getData();
        $customerAddressData->setCustomer($this->getUser())
                            ->setMyAddress($request->request->getBoolean('myAddress', false));

        $customerAddress = $createCustomerAddress->create($customerAddressData);

        return $this->setMessage('Customer address is added successfully!')->respond($customerAddress);
    }

    /**
     * @OA\Tag(name="Customer Address")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *         property="location",
     *         type="object",
     *         @OA\Property(property="lat", type="integer"),
     *         @OA\Property(property="long", type="integer")),
     *         @OA\Property(property="fullAddress", type="string"),
     *         @OA\Property(property="postalCode", type="integer"),
     *         @OA\Property(property="number", type="integer"),
     *         @OA\Property(property="province", type="integer"),
     *         @OA\Property(property="city", type="integer"),
     *         @OA\Property(property="district", type="integer"),
     *         @OA\Property(property="unit", type="integer"),
     *         @OA\Property(property="floor", type="integer"),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="family", type="string"),
     *         @OA\Property(property="nationalCode", type="string"),
     *         @OA\Property(property="mobile", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Update Customer Address",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Customer address is updated successfully!"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=CustomerAddress::class, groups={"default"})
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
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PUT"])]
    public function update(
        int $id,
        Request $request,
        UpdateCustomerAddressService $updateCustomerAddress
    ): JsonResponse {
        $customerAddress = $this->getCustomerAddress($id);

        $form = $this->createForm(CustomerAddressType::class, options: ['method' => 'PUT'])
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $customerAddressData = $form->getData();

        $customerAddress = $updateCustomerAddress->perform($customerAddress, $customerAddressData);

        return $this->setMessage('Customer address is updated successfully!')->respond($customerAddress);
    }

    /**
     * @OA\Tag(name="Customer Address")
     * @OA\Response(
     *     response=200,
     *     description="Delete a Customer Address.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="id", type="integer")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "destroy", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function destroy(int $id): JsonResponse
    {
        $customerAddress = $this->getCustomerAddress($id);

        $this->manager->remove($customerAddress);
        $this->manager->flush();

        return $this->respondEntityRemoved($id);
    }

    private function getCustomerAddress(int $id): CustomerAddress
    {
        $customerAddress = $this->customerAddressRepository->findOneBy([
            'id'       => $id,
            'customer' => $this->getUser()
        ]);

        if (!$customerAddress) {
            throw new AccessDeniedHttpException('Access Denied.');
        }

        return $customerAddress;
    }
}
