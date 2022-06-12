<?php

namespace App\Entity;

use App\Entity\Common\Blameable;
use App\Entity\Common\CodeAwareInterface;
use App\Entity\Common\Timestampable;
use App\Entity\Media\BrandImage;
use App\Repository\BrandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="brands")
 * @ORM\Entity(repositoryClass=BrandRepository::class)
 * @UniqueEntity(fields={"code"}, groups={"brand.create", "brand.update"})
 */
class Brand implements CodeAwareInterface
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({
     *     "default",
     *     "product.index",
     *     "product.show",
     *     "customer.product.show",
     *     "seller.product.brands_index",
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     *     "campaignCommission.show"
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"brand.create", "brand.update"})
     * @Assert\Length(min=2, max=255, groups={"brand.create", "brand.update"})
     * @Groups({
     *     "default",
     *     "product.index",
     *     "product.show",
     *     "customer.product.show",
     *     "seller.products.index",
     *     "seller.product.brands_index",
     *     "cart.show",
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     *     "campaignCommission.show"
     * })
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Assert\NotBlank(groups={"brand.create", "brand.update"})
     *
     * @Groups({
     *     "default",
     *     "product.index",
     *     "product.show",
     *     "customer.product.show",
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     * })
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255, unique=false)
     * @Assert\NotBlank(groups={"brand.create"})
     * @Assert\Length(min=2, max=255, groups={"brand.create", "brand.update"})
     * @Groups({"default", "customer.product.show"})
     */
    private $subtitle;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     * @Groups({"default", "customer.product.show"})
     */
    private $metaDescription;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="brand", orphanRemoval=true)
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity=BrandImage::class, mappedBy="entity", cascade={"persist","remove"}, fetch="EXTRA_LAZY")
     * @Assert\Valid(groups={"brand.create", "brand.update",})
     * @Groups({"default"})
     */
    private $image;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"default"})
     */
    private $description;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->image    = new ArrayCollection();
    }

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setBrand($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getBrand() === $this) {
                $product->setBrand(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImage(): ?BrandImage
    {
        $criteria = Criteria::create()
                            ->orderBy(['id' => Criteria::DESC,])
                            ->setFirstResult(0)
                            ->setMaxResults(1);

        return $this->image->matching($criteria)->first() ?: null;
    }

    /**
     * @param BrandImage $image
     * @return Brand
     */
    public function setImage(BrandImage $image): self
    {
        if (!$this->image->contains($image) && $image->getEntity() !== $this) {
            $this->image = [$image];
            $image->setEntity($this);
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
