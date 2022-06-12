<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Entity\Product;
use App\Entity\Wishlist;
use App\Form\WishlistType;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/wishlists", name: "wishlists.")]
class WishlistController extends Controller
{
    public function __construct(
        private EntityManagerInterface $manager,
        private WishlistRepository $wishlistRepository
    ) {
    }

    /**
     * @OA\Tag(name="Customer Wishlist")
     * @OA\Response(
     *     response=200,
     *     description="Return list of customer wishlists",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Wishlist::class, groups={"wishlist.read"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(): JsonResponse
    {
        return $this->respondWithPagination(
            $this->wishlistRepository->getAllByCustomerQuery($this->getUser()->getId()),
            context: ['groups' => 'wishlist.read']
        );
    }

    /**
     * @OA\Tag(name="Customer Wishlist")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="product", type="integer"),
     *     )
     * )
     *
     * @OA\Response(
     *     response=201,
     *     description="Add a product to customer wishlists",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Wishlist::class, groups={"wishlist.store"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "store", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function store(Product $product): JsonResponse
    {
        $form = $this->createForm(
            WishlistType::class,
            options: [
                'method'            => 'POST',
                'validation_groups' => 'customer.wishlist.add'
            ]
        );

        $form->submit(['customer' => $this->getUser()->getId(), 'product' => $product->getId()]);

        if ($form->isSubmitted() && $form->isValid()) {
            $wishlist = $form->getData();

            $this->manager->persist($wishlist);
            $this->manager->flush();

            return $this->respond($wishlist, Response::HTTP_CREATED, context: ['groups' => 'wishlist.store']);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Customer Wishlist")
     * @OA\Response(
     *     response=200,
     *     description="Delete a customer wishlist.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Entity has been removed successfully!"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="id", type="integer"),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "destroy", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function destroy(Product $product): JsonResponse
    {
        $wishlist = $this->wishlistRepository->findOneBy(['product' => $product, 'customer' => $this->getUser()]);

        if (!$wishlist) {
            return $this->respondWithError('Entity not found!', status: Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($wishlist);
        $this->manager->flush();

        return $this->respondEntityRemoved($product->getId());
    }
}
