<?php

namespace App\Controller\Landing;

use App\Controller\Controller;
use App\Entity\LandingIntro;
use App\Form\LandingIntroType;
use App\Service\Notification\DTOs\Customer\Campaign\BlackFriday\BlackFridayLandingSmsNotificationDto;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/black-friday", name: "black-friday.")]
class BlackFridayLandingController extends Controller
{
    public function __construct(private EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Campaign Landing")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="mobile", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Add a user's mobile",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Your message received successfully."),
     *         @OA\Property(property="results", type="object", @OA\Property(property="mobile", type="string")),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function store(Request $request, NotificationService $notificationService): JsonResponse
    {
        $form = $this->createForm(
            LandingIntroType::class,
            options: ['validation_groups' => 'landing.intro.store', 'method' => 'POST']
        );

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var LandingIntro $landing */
            $landing = $form->getData();

            $this->manager->persist($landing);
            $this->manager->flush();

            $notificationService->send(new BlackFridayLandingSmsNotificationDto($landing->getMobile()));

            return $this->respond(
                $landing,
                Response::HTTP_CREATED,
                context: ['groups' => 'landing.intro.store']
            );
        }

        return $this->respondValidatorFailed($form);
    }
}
