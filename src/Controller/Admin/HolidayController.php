<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Holiday;
use App\Form\HolidayType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route("/holidays", name: "holidays.")]
class HolidayController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Holiday")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[user.id]=10",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=user.id",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of holidays",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Holiday::class, groups={"default"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $builderFilterService): JsonResponse
    {
        return $this->respondWithPagination(
            $builderFilterService->filter(Holiday::class, $request->query->all())
        );
    }

    /**
     * @OA\Tag(name="Holiday")
     * @OA\Response(
     *     response=200,
     *     description="Return holiday details",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Holiday::class, groups={"default"}),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Holiday $holiday): JsonResponse
    {
        return $this->respond($holiday);
    }

    /**
     * @OA\Tag(name="Holiday")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=HolidayType::class)))
     * @OA\Response(
     *     response=201,
     *     description="Create a Holiday",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Holiday::class, groups={"default"})
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
    public function store(Request $request): JsonResponse
    {
        $form = $this->createForm(
            HolidayType::class,
            options: ['validation_groups' => 'holiday.create', 'method' => 'POST']
        );

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $holiday = $form->getData();

            $this->manager->persist($holiday);
            $this->manager->flush();

            return $this->respond($holiday, Response::HTTP_CREATED);
        }

        return $this->respondValidatorFailed($form);
    }


    /**
     * @OA\Tag(name="Holiday")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=HolidayType::class)))
     * @OA\Response(
     *     response=201,
     *     description="Update the Holiday",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Holiday::class, groups={"default"})
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
    public function update(Request $request, Holiday $holiday): JsonResponse
    {
        $form = $this->createForm(
            HolidayType::class,
            $holiday,
            ['method' => 'PATCH', 'validation_groups' => 'holiday.update']
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($holiday);
            $this->manager->flush();

            return $this->respond($holiday);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Holiday")
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
     *            @OA\Property(property="id", type="integer", description="Removed holiday id")
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "delete", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function delete(Holiday $holiday): JsonResponse
    {
        try {
            $holidayId = $holiday->getId();
            $this->manager->remove($holiday);
            $this->manager->flush();
        } catch (Throwable $exception) {
            return $this->respondWithError(
                'There is a problem at deleting holiday!',
                status: Response::HTTP_BAD_REQUEST
            );
        }

        return $this->respondEntityRemoved($holidayId);
    }
}
