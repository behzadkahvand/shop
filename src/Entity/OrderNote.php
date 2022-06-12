<?php

namespace App\Entity;

use App\Entity\Common\Timestampable;
use App\Repository\OrderNoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="order_notes")
 * @ORM\Entity(repositoryClass=OrderNoteRepository::class)
 */
class OrderNote
{
    use Timestampable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"order.notes.index", "order.notes.add"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="orderNotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class)
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"order.notes.index", "order.notes.add"})
     */
    private $admin;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(groups={"order.notes.add"})
     * @Assert\NotNull(groups={"order.notes.add"})
     * @Groups({"order.notes.index", "order.notes.add"})
     */
    private $description;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(?Admin $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }
}
