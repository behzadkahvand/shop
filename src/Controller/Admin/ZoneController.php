<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Zone;
use App\Form\ZoneType;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Zones\ZoneFormDataFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/zones", name: "zones.")]
class ZoneController extends Controller
{
    public function __construct(
        private EntityManagerInterface $manager,
        private ZoneFormDataFactoryInterface $zoneDataFactory
    ) {
    }

    /**
     * @OA\Tag(name="Zone")
     * @OA\Response(
     *     response=200,
     *     description="List of zones",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Zone::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $filterService): JsonResponse
    {
        return $this->respond($filterService->filter(Zone::class, $request->query->all()));
    }

    /**
     * @OA\Tag(name="Zone")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         required={"name", "code"},
     *         @OA\Property(property="code", type="string", minLength=1, maxLength=255),
     *         @OA\Property(property="name", type="string", minLength=1, maxLength=255),
     *         @OA\Property(
     *              property="provinces",
     *              type="array",
     *              description="array of province ids",
     *              @OA\Items(type="integer")
     *         ),
     *         @OA\Property(
     *              property="cities",
     *              type="array",
     *              description="array of city ids",
     *              @OA\Items(type="integer")
     *         ),
     *         @OA\Property(
     *              property="districts",
     *              type="array",
     *              description="array of district ids",
     *              @OA\Items(type="integer")
     *         ),
     *         @OA\Property(
     *              property="zones",
     *              type="array",
     *              description="array of zone ids",
     *              @OA\Items(type="integer")
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Zone successfully created, returns the newly created zone",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{type}", name: "store", methods: ["POST"])]
    public function store(Request $request, string $type): JsonResponse
    {
        $zoneData = $this->zoneDataFactory->create($type);

        if (empty($zoneData)) {
            $this->respondInvalidParameters('zone type is not valid.');
        }

        $zone = $zoneData->getZone();

        $form = $this->createForm(ZoneType::class, $zone, [
            'validation_groups' => 'zone.create',
            'method'            => 'POST',
            'type_class'        => $zoneData->getZonesFieldClass(),
            'type_field'        => $zoneData->getZonesFieldName(),
            'data_class'        => get_class($zone),
        ]);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($zone);
            $this->manager->flush();

            return $this->respond(statusCode: Response::HTTP_CREATED);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Zone")
     * @OA\Response(
     *     response=200,
     *     description="Zone successfully deleted",
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
    /*#[Route("/{id}", name: "delete", requirements: ["id" => "\d+"], methods: ["DELETE"])]
    public function delete(Zone $zone): JsonResponse
    {
        try {
            $zoneId = $zone->getId();
            $this->manager->remove($zone);
            $this->manager->flush();
        } catch (Throwable $exception) {
            return $this->respondWithError('message', [], Response::HTTP_BAD_REQUEST);
        }

        return $this->respondEntityRemoved($zoneId);
    }*/
}
