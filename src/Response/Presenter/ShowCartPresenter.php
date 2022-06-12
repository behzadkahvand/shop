<?php

namespace App\Response\Presenter;

use App\Entity\Cart;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class ShowCartPresenter
 */
final class ShowCartPresenter implements \JsonSerializable
{
    /**
     * @var int|null
     *
     * @Groups({"cart.show", "cart.shipments"})
     */
    private ?int $id;

    /**
     * @var int|null
     *
     * @Groups({"cart.show", "cart.shipments"})
     */
    private ?int $subTotal;

    /**
     * @var int|null
     *
     * @Groups({"cart.show", "cart.shipments"})
     */
    private ?int $grandTotal;

    /**
     * @var array
     */
    private array $messages;

    /**
     * @var array
     */
    private array $shipments;

    /**
     * ShowCartPresenter constructor.
     */
    private function __construct(Cart $cart, array $shipments)
    {
        $this->id         = $cart->getId();
        $this->subTotal   = $cart->getSubtotal();
        $this->grandTotal = $cart->getGrandTotal();
        $this->messages   = $cart->getMessages();
        $this->shipments  = $shipments;
    }

    /**
     * @param Cart  $cart
     * @param array $shipments
     *
     * @return static
     */
    public static function present(Cart $cart, array $shipments): self
    {
        return new self($cart, $shipments);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
