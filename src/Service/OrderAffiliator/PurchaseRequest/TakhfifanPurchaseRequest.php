<?php

namespace App\Service\OrderAffiliator\PurchaseRequest;

use App\Entity\Order;
use App\Service\OrderAffiliator\Exceptions\AffiliatorSendRequestException;
use App\Service\OrderAffiliator\Normalizer\TakhfifanPurchaseRequestDataNormalizer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TakhfifanPurchaseRequest implements AffiliatorPurchaseRequestInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const NAME = 'takhfifan';

    private const URL = 'https://analytics.takhfifan.com/track/purchase';

    protected HttpClientInterface $client;

    protected TakhfifanPurchaseRequestDataNormalizer $dataNormalizer;

    public function __construct(
        HttpClientInterface $client,
        TakhfifanPurchaseRequestDataNormalizer $dataNormalizer
    ) {
        $this->client         = $client;
        $this->dataNormalizer = $dataNormalizer;
    }

    public function send(Order $order): void
    {
        $data = $this->dataNormalizer->normalize($order);

        try {
            $result = $this->client->request(
                'POST',
                self::URL,
                [
                    'json'    => $data,
                    'headers' => [
                        'Accept'       => 'application/json',
                        'Content-Type' => 'application/json',
                    ]
                ]
            );
        } catch (\Throwable $exception) {
            $this->logger->error(
                'It has a problem on sending takhfifan affiliator purchase request',
                [
                    'exception_message' => $exception->getMessage(),
                    'exception_code'    => $exception->getCode()
                ]
            );

            throw new AffiliatorSendRequestException();
        }

        $statusCode = $result->getStatusCode();

        if ($statusCode != 201) {
            $this->logger->error(
                'It has a problem on sending takhfifan affiliator purchase request (status code not equals to 201)',
                [
                    'status'   => $statusCode,
                    'response' => json_decode($result->getContent(false), true)
                ]
            );

            throw new AffiliatorSendRequestException();
        }
    }
}
