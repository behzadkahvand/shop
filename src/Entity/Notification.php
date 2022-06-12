<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="notifications")
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 * @UniqueEntity(
 *     fields={"code", "section", "notificationType"},
 *     message="A notification with same section, code and type exists.",
 * )
 *
 * @ORM\Table(name="notifications", uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"code", "section", "notification_type"})
 * })
 */
class Notification
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"notification.index", "notification.show"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     * @Groups({"notification.index", "notification.show"})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=128)
     * @Groups({"notification.index", "notification.show"})
     */
    private $section;

    /**
     * @ORM\Column(type="text")
     * @Groups({"notification.index","notification.show"})
     */
    private $template;

    /**
     * @ORM\Column(type="string", length=16, name="notification_type")
     * @Groups({"notification.index", "notification.show"})
     */
    private $notificationType;

    /**
     * @var string[]
     * @ORM\Column(type="json")
     * @Groups({"notification.index", "notification.show"})
     */
    private $configuration = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(string $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getNotificationType(): ?string
    {
        return $this->notificationType;
    }

    public function setNotificationType(string $notificationType): self
    {
        $this->notificationType = $notificationType;

        return $this;
    }

    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function setConfiguration(array $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }
}
