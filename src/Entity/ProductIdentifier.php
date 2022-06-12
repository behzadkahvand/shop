<?php

namespace App\Entity;

use App\Repository\ProductIdentifierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="product_identifiers")
 * @ORM\Entity(repositoryClass=ProductIdentifierRepository::class)
 */
class ProductIdentifier
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     *
     * @Assert\Length(max=32)
     */
    private $identifier;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="productIdentifiers", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotNull()
     */
    private $product;

    /**
     * ProductIdentifier constructor.
     *
     * @param $identifier
     * @param $product
     */
    public function __construct(string $identifier = null, Product $product = null)
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }

        if ($product) {
            $product->addProductIdentifier($this);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
