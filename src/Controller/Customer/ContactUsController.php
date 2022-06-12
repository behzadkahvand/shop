<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use App\Form\ContactUsType;
use App\Service\ContactUs\ContactUsService;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/contact-us", name: "contact.us.")]
class ContactUsController extends Controller
{
    /**
     * @OA\Tag(name="Contact Us")
     * @OA\Parameter(
     *     name="Body Parameters",
     *     in="query",
     *     @OA\Schema(
     *         type="object",
     *         @OA\Property(property="subject", type="string"),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="email", type="string"),
     *         @OA\Property(property="phone", type="string"),
     *         @OA\Property(property="content", type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Update customer data",
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
    public function sendMail(Request $request, ContactUsService $contactUsService): JsonResponse
    {
        $form = $this->createForm(ContactUsType::class);

        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $this->respondValidatorFailed($form);
        }

        $contactUsService->sendMail($form->getData());

        return $this->setMessage('Your message received successfully.')->respond();
    }
}
