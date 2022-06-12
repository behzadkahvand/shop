<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Entity\WalletHistory;
use App\Service\ORM\QueryBuilderFilterService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Service\ORM\CustomFilters\Wallet\Customer\CustomerWalletHistoriesCustomFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route("/wallet", name: "wallet.")]
class WalletController extends Controller
{
    /**
     * @OA\Tag(name="Wallet")
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
     *
     * @OA\Response(
     *     response=200,
     *     description="Return customer wallet histories",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=WalletHistory::class, groups={"wallet_history.show"})),
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     * @ParamConverter("customFilter", options={
     *     "filters" = {
     *         CustomerWalletHistoriesCustomFilter::class,
     *     }
     * })
     */
    #[Route("/histories", name: "histories", methods: ["GET"])]
    public function histories(Request $request, QueryBuilderFilterService $filterer): JsonResponse
    {
        return $this->respondWithPagination(
            $filterer->filter(WalletHistory::class, $request->query->all()),
            context: ['groups' => ['wallet_history.show']]
        );
    }
}
