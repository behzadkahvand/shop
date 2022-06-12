<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Product;
use App\Entity\Seller;
use App\Service\Configuration\ConfigurationService;
use App\Service\ORM\CustomFilters\Product\Customer\TitleSearchCustomFilter;
use App\Service\Product\Search\Exceptions\SearchDataValidationException;
use App\Service\Product\Search\SearchData;
use App\Service\Product\Search\SellerProductSearchService;
use App\Service\Utils\Pagination\Pagination;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/sellers", name: "sellers.")]
class SellerController extends Controller
{
    /**
     * @OA\Tag(name="Seller")
     * @OA\Parameter(
     *     name="filter",
     *     in="query",
     *     description="Allow filtering response based on resource fields and relationships. example:
     *         filter[id]=10&filter[user.id]=10.
     *         valid keys: price, brandId, brand, category, title, available, original",
     *     @OA\Items(type="string")
     * )
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Allow sorting results based on resource fields or relationships. example:
     *         sort[]=-id&sort[]=user.id. valid values: price, mostVisited, bestSellers, latest, deliveryTime",
     *
     *     @OA\Items(type="string")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Return list of seller products",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Product::class, groups={"product.search.seller.filter", "media"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     *
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         TitleSearchCustomFilter::class
     *     }
     * })
     */
    #[Route("/{identifier}/products/search", name: "products.search", methods: ["GET"])]
    public function productSearch(
        Seller $seller,
        Request $request,
        SellerProductSearchService $searchService,
        ConfigurationService $configurationService
    ): JsonResponse {
        $data           = $request->query->all();
        $data['filter'] = $data['filter'] ?? [];

        $data['filter']['seller'] = $seller->getIdentifier();

        $page  = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);

        try {
            $searchResult = $searchService->search(
                new SearchData((array)$data['filter'], (array)($data['sort'] ?? [])),
                new Pagination($page, $limit)
            );
        } catch (SearchDataValidationException $e) {
            return $this->respondInvalidParameters($e->getMessage());
        }

        $metas           = $searchResult->getMetas();
        $metas['seller'] = [
            'name'              => $seller->getName(),
            'createdAt'         => $seller->getCreatedAt(),
            'score'             => $seller->getScore(),
            'sellerScoreLevels' => $configurationService->findByCode(ConfigurationCodeDictionary::SELLER_SCORE_LEVELS)?->getValue() ?? []
        ];

        return $this->setMetas($metas)
                    ->respond(
                        $searchResult->getResults(),
                        context: [
                            'groups' => ['product.search.seller.filter', 'media'],
                        ]
                    );
    }
}
