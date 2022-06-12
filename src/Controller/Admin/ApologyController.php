<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Apology;
use App\Form\ApologyType;
use App\Service\Apology\ApologyCreator;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/apologies", name: "apologies.")]
class ApologyController extends Controller
{
    /**
     * @OA\Tag(name="Apology")
     * @OA\Response(
     *     response=200,
     *     description="Return list of apologies.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Apology::class, groups={"apology.read", "promotion.read"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $service): JsonResponse
    {
        return $this->respondWithPagination(
            $service->filter(Apology::class, $request->query->all()),
            context: ['groups' => ['apology.read', 'promotion.read']]
        );
    }

    /**
     * @OA\Tag(name="Apology")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="codePrefix", type="string"),
     *         @OA\Property(property="messageTemplate", type="string"),
     *         @OA\Property(property="promotion", type="integer"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Apology successfully created, returns the newly created apology.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Apology::class, groups={"apology.read", "promotion.read"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ApologyCreator $apologyCreator
    ): JsonResponse {
        $form = $this->createForm(ApologyType::class, [], [
            'validation_groups' => 'apology.create',
        ]);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $apology = $apologyCreator->create(
                $form->get('promotion')->getData(),
                $form->get('codePrefix')->getData(),
                $form->get('messageTemplate')->getData()
            );

            $entityManager->flush();

            return $this->respond(
                $apology,
                Response::HTTP_CREATED,
                context: ['groups' => ['apology.read', 'promotion.read']]
            );
        }

        return $this->respondValidatorFailed($form, false);
    }

    /**
     * @OA\Tag(name="Apology")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="codePrefix", type="string"),
     *         @OA\Property(property="messageTemplate", type="string"),
     *         @OA\Property(property="promotion", type="integer"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Apology successfully updated, returns the updated apology.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Apology::class, groups={"apology.read", "promotion.read"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(Request $request, Apology $apology, EntityManagerInterface $entityManager): JsonResponse
    {
        $form = $this->createForm(
            ApologyType::class,
            [],
            [
                'validation_groups' => 'apology.update',
                'method'            => 'PATCH',
            ]
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $apology->update($form->getData());
            $entityManager->flush();

            return $this->respond($apology, context: ['groups' => ['apology.read', 'promotion.read']]);
        }

        return $this->respondValidatorFailed($form);
    }
}
