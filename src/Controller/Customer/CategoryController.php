<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/categories", name: "categories.")]
class CategoryController extends Controller
{
    /**
     * @OA\Tag(name="Category")
     * @OA\Response(
     *     response=200,
     *     description="Return root categories.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Category::class, groups={"customer.show.root.categories"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/roots", name: "show.roots", methods: ["GET"])]
    public function showRoots(CategoryRepository $categoryRepository): JsonResponse
    {
        return $this->respond(
            $categoryRepository->getRootCategories(),
            context: ['groups' => ['customer.show.root.categories'],]
        );
    }
}
