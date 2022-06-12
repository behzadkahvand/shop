<?php

namespace App\DTO\Customer;

use App\Validator\Mobile;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Component\Validator\Constraints as Assert;

final class ContactUsData
{
    /**
     * @Assert\NotBlank()
     */
    public string $subject;

    /**
     * @Assert\NotBlank()
     */
    public string $name;

    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public string $email;

    /**
     * @Assert\NotBlank()
     * @Mobile(message="This value is not a valid mobile number.")
     */
    public string $phone;

    /**
     * @Assert\NotBlank()
     */
    public string $content;

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return collect($this->toArray())->transform(fn($value, $key) => "$key: $value")->implode(PHP_EOL);
    }
}
