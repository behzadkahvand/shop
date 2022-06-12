<?php

namespace App\Response\Auth;

use App\Entity\Customer;
use Symfony\Component\HttpFoundation\JsonResponse;

class OtpVerifyResponse extends JsonResponse
{
    private array $originalData;

    public static function createResponse(JsonResponse $jsonResponse): self
    {
        $data = json_decode($jsonResponse->getContent(), true);
        $response = new static($data);
        $response->originalData = $data;

        return $response;
    }

    public function toArray(): array
    {
        return $this->originalData;
    }
}
