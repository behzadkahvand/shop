<?php

namespace App\Entity\Media;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MediaRepository::class)
 * @ORM\Table(name="medias")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"productGallery" = ProductGallery::class,
 *     "featuredImage" = ProductFeaturedImage::class,
 *     "categoryImage":CategoryImage::class,
 *     "brandImage":BrandImage::class
 *     })
 */
abstract class Media
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"create","categories.store","update","brand.create", "seller.product.create", "brand.update",})
     *
     * @Groups({
     *     "default",
     *     "media",
     *     "orderShipment.show","wishlist.read","notify.read",
     *     "customer.order.show",
     *     "cart.show",
     *     "cart.shipments",
     *     "image.create",
     *     "seller.order.items.index",
     *     "seller.products.index",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "admin.seller.order.items.index",
     *     "order.show",
     *     "admin.seller.order.items.update_status",
     *     "seller.product.create",
     *     "seller.package.show",
     *     "order.items",
     *     "customer.rateAndReview.products",
     *     "customer.rateAndReview.index",
     *     "product.search",
     *     "customer.product.show",
     *     "product.index.media",
     *     "customer.layout.onSaleBlocks",
     * })
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({
     *     "default",
     *     "media",
     *     "orderShipment.show","wishlist.read","notify.read",
     *     "customer.order.show",
     *     "cart.show",
     *     "cart.shipments",
     *     "order.show",
     *     "seller.product.create",
     *     "order.items",
     *     "customer.rateAndReview.products",
     *     "customer.rateAndReview.index",
     *     "product.search",
     *     "customer.product.show",
     *     "product.index.media",
     *     "customer.layout.onSaleBlocks",
     * })
     */
    private $alt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable = true)
     */
    protected $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }
}
