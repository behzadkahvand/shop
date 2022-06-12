<?php

namespace App\Service\Order\Survey\Link;

use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class PorslineOrderSurveyLinkGenerator
 */
final class PorslineOrderSurveyLinkGenerator implements SurveyLinkGeneratorInterface
{
    public const PORSLINE_ORDER_SURVEY_API = 'https://survey.porsline.ir/api/surveys/247553/variables/hashes/';

    private HttpClientInterface $httpClient;

    private string $apiKey;

    /**
     * PorslineOrderSurveyLinkGenerator constructor.
     *
     * @param HttpClientInterface $httpClient
     * @param string $porslineApiKey
     */
    public function __construct(HttpClientInterface $httpClient, string $porslineApiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $porslineApiKey;
    }

    /**
     * @inheritDoc
     */
    public function generateLink(string $orderIdentifier): string
    {
        $response = $this->httpClient->request('POST', self::PORSLINE_ORDER_SURVEY_API, [
            'json'    => [
                'values' => [
                    ['identifier' => $orderIdentifier],
                ],
            ],
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => "API-Key {$this->apiKey}",
                'Accept'        => 'application/json',
            ],
        ]);

        $result = json_decode($response->getContent(false), true, 512);

        if (null === $result || !isset($result['urls']) || empty($result['urls'])) {
            throw new RuntimeException('Unable to generate survey link. porsline api returned an unexpected response.');
        }

        return current($result['urls']);
    }
}
