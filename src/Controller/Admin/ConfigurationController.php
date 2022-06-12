<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Configuration;
use App\Form\ConfigurationType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/configurations", name: "configurations.")]
class ConfigurationController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Configuration")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(
     *             property="configs",
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="code", type="string"),
     *                 @OA\Property(property="value", type="string", description="JSON encoded representaiton of
     *                                                 value"),
     *             )
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Configuration successfully created, returns the newly created configurations",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Configuration::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/bulk", name: "store_bulk", methods: ["POST"])]
    public function bulk(Request $request): JsonResponse
    {
        $form = $this->createFormBuilder()->add('configs', CollectionType::class, [
            'entry_type'         => ConfigurationType::class,
            'allow_add'          => true,
            'method'             => 'POST',
            'allow_extra_fields' => true,
        ])->getForm()->submit($request->request->all());

        if ($form->isValid()) {
            $configs = $form['configs']->getData();
            foreach ($configs as $configuration) {
                $this->manager->persist($configuration);
            }

            $this->manager->flush();

            return $this->respond($configs, Response::HTTP_CREATED);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Configuration")
     * @OA\Response(
     *     response=200,
     *     description="Return list of configurations",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *             property="results",
     *             type="array",
     *             @OA\Items(ref=@Model(type=Configuration::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request, QueryBuilderFilterService $builderFilter): JsonResponse
    {
        return $this->respond(
            $builderFilter->filter(Configuration::class, $request->query->all())->getQuery()->getResult(),
            context: ['groups' => 'configurations.grid']
        );
    }
}
