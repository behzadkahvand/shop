<?php

namespace App\Controller\Customer;

use App\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MediaController extends Controller
{
    /**
     * This route and it's uri is not meant to be handled by framework. it will be handled by web server.
     * existence of this route is only for normalizing media entities.
     *
     * @see \App\Serializer\Normalizer\MediaNormalizer::getBaseUri()
     */
    #[Route("", name: "media", methods: ["GET"])]
    public function show()
    {
        throw new NotFoundHttpException('Oops! We can\'t find what you are looking for!');
    }
}
