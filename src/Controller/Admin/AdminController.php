<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\DTO\Admin\ChangePasswordData;
use App\Entity\Admin;
use App\Events\Auth\UserCredentialsChanged;
use App\Events\Auth\UserDeactivated;
use App\Form\AdminType;
use App\Form\Type\Admin\ChangePasswordType;
use App\Service\ORM\QueryBuilderFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/admins", name: "admin.")]
class AdminController extends Controller
{
    public function __construct(
        private EntityManagerInterface $manager,
        private QueryBuilderFilterService $filter
    ) {
    }

    /**
     * @OA\Tag(name="Admin")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="family", type="string"),
     *         @OA\Property(property="mobile", type="string"),
     *         @OA\Property(property="password", type="string"),
     *         @OA\Property(property="permissions", type="array", @OA\Items(type="string")),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Created admin",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Admin::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $form = $this->createForm(
            AdminType::class,
            null,
            ['validation_groups' => 'admin.create', 'method' => 'POST']
        );

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $admin = $form->getData();
            $admin->setIsActive(true);
            $admin->setPassword($hasher->hashPassword($admin, $admin->getPlainPassword()));
            $admin->eraseCredentials();
            $this->manager->persist($admin);
            $this->manager->flush();

            return $this->respond($admin, Response::HTTP_CREATED);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Admin")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="family", type="string"),
     *         @OA\Property(property="isActive", type="boolean"),
     *         @OA\Property(property="mobile", type="string"),
     *         @OA\Property(property="password", type="string"),
     *         @OA\Property(property="permissions", type="array", @OA\Items(type="string")),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Updated admin",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Admin::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "update", requirements: ["id" => "\d+"], methods: ["PATCH"])]
    public function update(
        Request $request,
        UserPasswordHasherInterface $hasher,
        Admin $admin,
        EventDispatcherInterface $dispatcher
    ): JsonResponse {
        $adminWasActive = $admin->isActive();

        $form = $this->createForm(
            AdminType::class,
            $admin,
            ['validation_groups' => 'admin.update', 'method' => 'PATCH']
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($admin->getPlainPassword()) {
                $admin->setPassword($hasher->hashPassword($admin, $admin->getPlainPassword()));
                $admin->eraseCredentials();
                $dispatcher->dispatch(new UserCredentialsChanged($admin));
            }
            $this->manager->persist($admin);
            $this->manager->flush();

            if (!$admin->isActive() && $adminWasActive) {
                $dispatcher->dispatch(new UserDeactivated($admin));
            }

            return $this->respond($admin);
        }

        return $this->respondValidatorFailed($form);
    }

    /**
     * @OA\Tag(name="Admin")
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
     *     description="Return list of admins",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Admin::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "index", methods: ["GET"])]
    public function index(Request $request): JsonResponse
    {
        return $this->respondWithPagination($this->filter->filter(Admin::class, $request->query->all()));
    }


    /**
     * @OA\Tag(name="Admin")
     * @OA\Response(
     *     response=200,
     *     description="Return a admin details.",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=Admin::class, groups={"default"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/{id}", name: "show", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Admin $admin): JsonResponse
    {
        return $this->respond($admin, context: ['groups' => ['default']]);
    }

    /**
     * @OA\Tag(name="Admin")
     * @OA\Parameter(name="Body Parameters", in="query", @OA\Schema(ref=@Model(type=ChangePasswordType::class)))
     * @OA\Response(
     *     response=200,
     *     description="Admin password is changed!",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="array",
     *            @OA\Items(ref=@Model(type=Admin::class, groups={"default"}))
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route("/change-password", name: "change.password", methods: ["POST"])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EventDispatcherInterface $dispatcher
    ): JsonResponse {
        $form = $this->createForm(ChangePasswordType::class)
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }
        $admin = $this->getUser();

        /** @var ChangePasswordData $data */
        $data = $form->getData();

        $admin->setPassword($hasher->hashPassword($admin, $data->getNewPassword()));

        $dispatcher->dispatch(new UserCredentialsChanged($admin));

        $this->manager->persist($admin);
        $this->manager->flush();

        return $this->respond($admin);
    }
}
