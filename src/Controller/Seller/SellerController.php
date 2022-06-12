<?php

namespace App\Controller\Seller;

use App\Controller\Controller;
use App\Dictionary\ConfigurationCodeDictionary;
use App\Entity\Seller;
use App\Repository\SellerRepository;
use App\Service\Configuration\ConfigurationService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SellerController extends Controller
{
    /**
     * @OA\Tag(name="Best sellers")
     *
     * @OA\Response(
     *     response=200,
     *     description="Return list of best sellers",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Seller::class, groups={"seller.best_sellers"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="sellerScoreLevels", type="string"))
     *     )
     * )
     */
    #[Route("/best-sellers", name: "best_sellers", methods: ["GET"])]
    public function bestSellers(SellerRepository $sellerRepository, ConfigurationService $configurationService): JsonResponse
    {
        $this->setMetas(
            [
                'sellerScoreLevels' => $configurationService->findByCode(ConfigurationCodeDictionary::SELLER_SCORE_LEVELS)?->getValue() ?? []
            ]
        );

        return $this->respond(
            $sellerRepository->getBestSellers(10),
            context: ['groups' => ['seller.best_sellers']]
        );
    }
}
