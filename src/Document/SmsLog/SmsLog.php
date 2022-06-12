<?php

namespace App\Document\SmsLog;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class SmsLog
{
    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @MongoDB\Id @MongoDB\Index
     */
    protected $id;

    /**
     * @MongoDB\Field(name="interface", type="string")
     */
    protected $interface;

    /**
     * @MongoDB\EmbedOne(targetDocument=Recipient::class)
     */
    protected $recipient;

    /**
     * @MongoDB\Field(name="content", type="string")
     */
    protected $content;

    /**
     * @MongoDB\Field(name="code", type="string")
     */
    protected string $code;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $createdAt;

    public function setRecipient(Recipient $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function setInterface(string $interface): self
    {
        $this->interface = $interface;

        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param string $code
     * @return SmsLog
     */
    public function setCode(string $code): SmsLog
    {
        $this->code = $code;
        return $this;
    }
}
