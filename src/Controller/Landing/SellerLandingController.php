<?php

namespace App\Controller\Landing;

use App\Controller\Controller;
use App\Entity\MarketingSellerLanding;
use App\Form\SellerFormType;
use App\Messaging\Messages\Command\Notification\Recipient;
use App\Service\Notification\DTOs\Seller\SellerLandingSmsNotificationDTO;
use App\Service\Notification\NotificationService;
use App\Service\SellerForm\SellerFormService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/seller-form", name: "seller.form.")]
class SellerLandingController extends Controller
{
    /**
     * @OA\Tag(name="Seller Form")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="categories", type="array", @OA\Items(type="integer")),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="phone", type="string"),
     *         @OA\Property(property="company", type="string"),
     *         @OA\Property(property="ownershipType", type="string"),
     *         @OA\Property(property="commodityDiversity", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Seller form",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="succeed", type="boolean"),
     *         @OA\Property(property="message", type="string", default="Your message received successfully."),
     *         @OA\Property(property="results", type="string"),
     *         @OA\Property(property="metas", type="object", @OA\Property(property="key", type="string"))
     *     )
     * )
     */
    #[Route(name: "send.mail", methods: ["POST"])]
    public function sendMail(
        Request $request,
        SellerFormService $contactUsService,
        NotificationService $notificationService,
        EntityManagerInterface $manager
    ): JsonResponse {
        $form = $this->createForm(SellerFormType::class);

        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        /** @var MarketingSellerLanding $marketingSellerLanding */
        $marketingSellerLanding = $form->getData();

        $contactUsService->sendMail($marketingSellerLanding);
        $recipient = new Recipient(
            $form['phone']->getData(),
            $form['name']->getData(),
        );
        $notificationService->send(
            new SellerLandingSmsNotificationDTO($recipient)
        );

        $manager->persist($marketingSellerLanding);
        $manager->flush();

        return $this->setMessage('Your message received successfully.')->respond();
    }
}
