<?php

namespace App\Entity;

use App\Repository\OrderCancelReasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="order_cancel_reason_apology")
 * @ORM\Entity(repositoryClass=OrderCancelReasonApologyRepository::class)
 *
 */
class OrderCancelReasonApology
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=OrderCancelReason::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private OrderCancelReason $orderCancelReason;

    /**
     * @ORM\ManyToOne(targetEntity=Apology::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private Apology $apology;

    /**
     * OrderCancelReasonApology constructor.
     * @param OrderCancelReason $orderCancelReason
     * @param Apology $apology
     */
    public function __construct(OrderCancelReason $orderCancelReason, Apology $apology)
    {
        $this->orderCancelReason = $orderCancelReason;
        $this->apology = $apology;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Apology
     */
    public function getApology(): Apology
    {
        return $this->apology;
    }
}
