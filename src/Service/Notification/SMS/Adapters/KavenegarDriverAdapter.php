<?php

namespace App\Service\Notification\SMS\Adapters;

use App\Service\Notification\SMS\SmsDriverInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class KavenegarDriverAdapter implements SmsDriverInterface
{
    protected string $smsProviderApi;

    protected string $smsProviderApiKey;

    protected string $smsProviderSenderNumber;

    protected HttpClientInterface $client;

    public function __construct(
        string $smsProviderApi,
        string $smsProviderApiKey,
        string $smsProviderSenderNumber,
        HttpClientInterface $client
    ) {
        $this->smsProviderApi = $smsProviderApi;
        $this->smsProviderApiKey = $smsProviderApiKey;
        $this->smsProviderSenderNumber = $smsProviderSenderNumber;
        $this->client = $client;

        $this->addApiKeyToApiUrl();
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendMessage(string $mobile, string $message): void
    {
        $this->client->request('POST', $this->smsProviderApi, [
            'body' => [
                'receptor' => $mobile,
                'message' => $message,
                'sender' => $this->smsProviderSenderNumber,
            ],
        ]);
    }

    public static function getName(): string
    {
        return 'Kavenegar';
    }

    private function addApiKeyToApiUrl(): void
    {
        // https://api.kavenegar.com/v1/{API-KEY}/sms/send.json
        $this->smsProviderApi = str_replace('{API-KEY}', $this->smsProviderApiKey, $this->smsProviderApi);
    }
}
