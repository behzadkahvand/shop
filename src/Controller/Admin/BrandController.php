<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Brand;
use App\Form\BrandType;
use App\Service\ORM\CustomFilters\Brand\MultiColumnSearchCustomFilter;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route("/brands", name: "brands.")]
class BrandController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Brand")
     * @OA\Response(
     *     response=200,
     *     description="Return list of brands",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Brand::class, groups={"default"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         MultiColumnSearchCustomFilter::class
     *     }
     * })
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $builderFilter): JsonResponse
    {
        return $this->respondWithPagination(
            $builderFilter->filter(Brand::class, $request->query->all()),
            context: ['groups' => ['default', 'media']]
        );
    }

    /**
     * @OA\Tag(name="Brand")
     * @OA\Response(
     *     response=200,
     *     description="Return a Brand details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Brand::class, groups={"default"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Brand $brand): JsonResponse
    {
        return $this->respond($brand, context: ['groups' => ['default', 'media']]);
    }

    /**
     * @OA\Tag(name="Brand")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=BrandType::class, groups={"brand.create"})))
     * @OA\Response(
     *     response=201,
     *     description="Brand successfully created, returns the newly created brand",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Brand::class, groups={"default"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(
            BrandType::class,
            options: [
                'validation_groups' => 'brand.create',
                'method'            => 'POST',
            ]
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $brand = $form->getData();

            $this->manager->persist($brand);
            $this->manager->flush();

            return $this->respond($brand, Response::HTTP_CREATED);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Brand")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=BrandType::class, groups={"brand.update"})))
     * @OA\Response(
     *     response=200,
     *     description="Brand successfully updated, returns the newly updated brand.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Brand::class, groups={"default"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(Request $request, Brand $brand): JsonResponse
    {
        $form = $this->createForm(
            BrandType::class,
            $brand,
            [
                'validation_groups' => 'brand.update',
                'method'            => 'PATCH',
            ]
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($brand);
            $this->manager->flush();

            return $this->respond($brand);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Brand")
     * @OA\Response(
     *     response=200,
     *     description="Brand successfully deleted",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            @OA\Property(property="id", type="integer"),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "delete", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function delete(Brand $brand): JsonResponse
    {
        try {
            $brandId = $brand->getId();
            $this->manager->remove($brand);
            $this->manager->flush();
        } catch (Throwable $exception) {
            return $this->respondWithError('message', status: Response::HTTP_BAD_REQUEST);
        }

        return $this->respondEntityRemoved($brandId);
    }
}
