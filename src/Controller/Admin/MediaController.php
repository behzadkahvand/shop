<?php

namespace App\Controller\Admin;

use App\Controller\Controller;
use App\Entity\Media\ProductFeaturedImage;
use App\Form\UploadMediaType;
use App\Service\Media\UploadHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/media", name: "media.")]
class MediaController extends Controller
{
    #[Route("/{type}/upload", name: "upload", requirements: ["type" => "brand|category|product-image|product-gallery|product-content"], methods: ["POST"])]
    public function uploadFile(Request $request, UploadHelper $uploadHelper, string $type): JsonResponse
    {
        $form = $this->createForm(UploadMediaType::class, null, compact('type'))
                     ->submit($request->files->all());

        if ($form->isValid()) {
            $media        = $form->getData();
            $uploadedFile = $form['imageFile']->getData();

            if ($uploadedFile) {
                $newFilename = $uploadHelper->uploadImage($uploadedFile, $type);

                /**
                 * Just for serialization process
                 *
                 * @see \App\Serializer\Normalizer\MediaNormalizer::supportsNormalization()
                 */
                $image = new ProductFeaturedImage();

                $media->setImageFileName($newFilename);
                $media->setMedia($image->setPath($newFilename));
            }

            return $this->respond($media, Response::HTTP_CREATED, context: ['groups' => ['image.create']]);
        }

        return $this->respondValidatorFailed($form);
    }
}
