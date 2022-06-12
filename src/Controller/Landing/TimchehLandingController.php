<?php

namespace App\Controller\Landing;

use App\Controller\Controller;
use App\Entity\ConsultationRequest;
use App\Form\ConsultationRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/consultation-request", name: "consultation-request.")]
class TimchehLandingController extends Controller
{
    public function __construct(protected EntityManagerInterface $manager)
    {
    }

    /**
     * @OA\Tag(name="Timcheh-organization Landing")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="fullName", type="string"),
     *         @OA\Property(property="phone", type="string"),
     *         @OA\Property(property="organization", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Add a consultation request",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(
     *            property="results",
     *            type="object",
     *            ref=@Model(type=ConsultationRequest::class, groups={"default"})
     *         ),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "store", methods: ["POST"])]
    public function storeConsultationRequest(Request $request): JsonResponse
    {
        $form = $this->createForm(ConsultationRequestFormType::class)
                     ->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }
        /** @var ConsultationRequest $consultationRequest */
        $consultationRequest = $form->getData();

        $this->manager->persist($consultationRequest);
        $this->manager->flush();

        return $this->setMessage('Your request has been received successfully.')
                    ->respond($consultationRequest, Response::HTTP_CREATED);
    }
}
