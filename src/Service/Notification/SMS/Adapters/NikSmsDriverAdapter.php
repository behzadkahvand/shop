<?php

namespace App\Service\Notification\SMS\Adapters;

use App\Service\Notification\SMS\SmsDriverInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NikSmsDriverAdapter implements SmsDriverInterface
{
    protected string $smsProviderApi;

    protected string $smsProviderUsername;

    protected string $smsProviderPassword;

    protected string $smsProviderSenderNumber;

    protected HttpClientInterface $client;

    public function __construct(
        string $smsProviderApi,
        string $smsProviderUsername,
        string $smsProviderPassword,
        string $smsProviderSenderNumber,
        HttpClientInterface $client
    ) {
        $this->smsProviderApi = $smsProviderApi;
        $this->smsProviderUsername = $smsProviderUsername;
        $this->smsProviderPassword = $smsProviderPassword;
        $this->smsProviderSenderNumber = $smsProviderSenderNumber;
        $this->client = $client;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendMessage(string $mobile, string $message): void
    {
        $this->client->request('POST', $this->smsProviderApi, [
            'body' => [
                'login' => $this->smsProviderUsername,
                'pass' => $this->smsProviderPassword,
                'msgBody' => $message,
                'recNums' => $mobile,
                'senderNum' => $this->smsProviderSenderNumber,
                'send' => '1',
            ],
        ]);
    }

    public static function getName(): string
    {
        return 'NikSms';
    }
}
