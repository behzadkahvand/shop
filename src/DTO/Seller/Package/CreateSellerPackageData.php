<?php

namespace App\DTO\Seller\Package;

use App\Entity\SellerOrderItem;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as AppAssert;

/**
 * Class CreateSellerPackageData
 */
final class CreateSellerPackageData
{
    /**
     * @var ArrayCollection|SellerOrderItem[]
     *
     * @Assert\Count(min=1)
     * @Assert\All({@AppAssert\SellerOrderItem(message="One of items does not belong to current user")})
     */
    private $items;


    /**
     * @Assert\NotBlank(message="Package type can not be blank.")
     * @Assert\Choice(
     *     callback={"App\Dictionary\SellerPackageType", "toArray"},
     *     message="Package type does not have valid value."
     * )
     */
    private $type;

    /**
     * @return SellerOrderItem[]|array|null
     */
    public function getItems(): ?array
    {
        return $this->items ?? null;
    }

    /**
     * @param iterable|array $items
     *
     * @return CreateSellerPackageData
     */
    public function setItems($items): CreateSellerPackageData
    {
        $this->items = collect($items)->toArray();

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
