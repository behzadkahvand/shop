<?php

namespace App\Messaging\Serializers\Command;

use App\Messaging\Messages\Command\AbstractAsyncMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

final class AsyncMessageSerializer implements SerializerInterface
{
    public function __construct(private PhpSerializer $serializer)
    {
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        return $this->serializer->decode($encodedEnvelope);
    }

    public function encode(Envelope $envelope): array
    {
        $envelopeMessage = $envelope->getMessage();

        if (!($envelopeMessage instanceof AbstractAsyncMessage)) {
            return $this->serializer->encode($envelope);
        }

        $stamps  = array_merge(...array_values($envelope->all()));
        $message = $envelopeMessage->getWrappedMessage();

        return $this->serializer->encode(Envelope::wrap($message, $stamps));
    }
}
