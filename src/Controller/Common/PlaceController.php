<?php

namespace App\Controller\Common;

use App\Controller\Controller;
use App\Entity\City;
use App\Entity\District;
use App\Entity\Province;
use App\Service\ORM\QueryBuilderFilterService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

#[Route("/places", name: "places.")]
class PlaceController extends Controller
{
    public function __construct(
        private QueryBuilderFilterService $filterService,
        private CacheInterface $cache
    ) {
    }

    /**
     * @OA\Tag(name="Place")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[cities.id]=10",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=cities.id",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of provinces",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Province::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/provinces", name: "provinces", methods: ["GET"])]
    public function provinces(Request $request): JsonResponse
    {
        $context = $request->query->all();

        $data = $this->cache->get(
            'places_provinces_' . md5(serialize($context)),
            function (CacheItemInterface $item) use ($context) {
                $qb = $this->filterService->filter(Province::class, $context);
                [$rootAlias] = $qb->getRootAliases();

                $qb->select("$rootAlias.id", "$rootAlias.code", "$rootAlias.name");

                $data = array_map(fn($data) => $this->convertIdTypeToInt($data), $qb->getQuery()->getScalarResult());

                $item->set($data);
                $item->expiresAfter(6 * 60 * 60);

                return $data;
            }
        );

        return $this->respond($data);
    }

    /**
     * @OA\Tag(name="Place")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[customerAddresses.id]=10",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=customerAddresses.id",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of cities",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=City::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/cities", name: "places.cities", methods: ["GET"])]
    public function cities(Request $request): JsonResponse
    {
        $context = $request->query->all();
        $data    = $this->cache->get(
            'places_cities_' . md5(serialize($context)),
            function (CacheItemInterface $item) use ($context) {
                $qb = $this->filterService->filter(City::class, $context);
                [$rootAlias] = $qb->getRootAliases();

                $qb->select("$rootAlias.id", "$rootAlias.name");

                $data = array_map(fn($data) => $this->convertIdTypeToInt($data), $qb->getQuery()->getScalarResult());

                $item->set($data);
                $item->expiresAfter(6 * 60 * 60);

                return $data;
            }
        );

        return $this->respond($data);
    }

    /**
     * @OA\Tag(name="Place")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *     filter[id]=10&filter[city.id]=10",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *     sort[]=-id&sort[]=city.id",
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of districts",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=District::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/districts", name: "places.districts", methods: ["GET"])]
    public function districts(Request $request): JsonResponse
    {
        $context = $request->query->all();

        $data = $this->cache->get(
            'places_districts_' . md5(serialize($context)),
            function (CacheItemInterface $item) use ($context) {
                $qb = $this->filterService->filter(District::class, $context);
                [$rootAlias] = $qb->getRootAliases();
                $qb->select("$rootAlias.id", "$rootAlias.name");

                $data = array_map(fn($data) => $this->convertIdTypeToInt($data), $qb->getQuery()->getScalarResult());

                $item->set($data);
                $item->expiresAfter(6 * 60 * 60);

                return $data;
            }
        );

        return $this->respond($data);
    }

    private function convertIdTypeToInt(array $data): array
    {
        $data['id'] = (int)$data['id'];

        return $data;
    }
}
