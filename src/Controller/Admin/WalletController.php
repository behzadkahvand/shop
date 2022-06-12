<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Customer;
use App\Entity\WalletHistory;
use App\Form\Type\Admin\ChangeWalletOwnerType;
use App\Service\ORM\QueryBuilderFilterService;
use App\Service\Utils\Error\ErrorExtractor;
use App\Service\Wallet\ChangeWalletOwnerService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/", name: "wallet.")]
class WalletController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

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
     *     description="Return list of wallet histories",
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
     */
    #[Route("/customers/{id}/wallet-histories", name: "histories", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function histories(Request $request, Customer $customer, QueryBuilderFilterService $filterer): JsonResponse
    {
        $query = array_replace_recursive($request->query->all(), [
            'filter' => [
                'wallet.id' => $customer->getWallet()->getId(),
            ],
            'sort'   => ['-createdAt']
        ]);

        return $this->respondWithPagination(
            $filterer->filter(WalletHistory::class, $query),
            context: ['groups' => ['wallet_history.show']]
        );
    }

    /**
     * @OA\Tag(name="Wallet")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="currentOwner", type="string"),
     *         @OA\Property(property="newOwner", type="string")
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Wallet owner has changed",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/wallets/change-owner", name: "change_owner", methods: ["POST"])]
    public function changeWalletOwner(
        Request $request,
        ChangeWalletOwnerService $changeWalletOwnerService
    ): JsonResponse {
        $form = $this->createForm(ChangeWalletOwnerType::class)->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $currentOwner = $form->get('currentOwner')->getData();
        $newOwner     = $form->get('newOwner')->getData();

        $changeWalletOwnerService->change($currentOwner, $newOwner);

        $this->manager->flush();

        return $this->setMessage("Wallet owner has changed")->respond();
    }

    /**
     * @OA\Tag(name="Wallet")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="freeze", type="boolean"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Wallet state has changed",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/customers/{id}/wallet/suspension", name: "wallet_suspension", requirements: ["id" => "\d+"], methods: ["POST"])]
    public function walletSuspension(
        Request $request,
        Customer $customer,
        ValidatorInterface $validator,
        ErrorExtractor $errorExtractor,
    ): JsonResponse {
        $violations = $validator->validate($request->request->all(), new Collection([
            'fields' => [
                'freeze' => [
                    new NotNull(),
                ],
            ],
        ]));

        if (count($violations) > 0) {
            $errors = $errorExtractor->extract($violations);

            return $this->respondWithError(
                'Validation error has been detected!',
                $errors,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $customer->getWallet()->setIsFrozen($request->get('freeze'));

        $this->manager->flush();

        return $this->setMessage("Wallet state has changed")->respond();
    }
}
