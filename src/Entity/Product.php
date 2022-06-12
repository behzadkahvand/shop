<?php

namespace App\Entity;

use App\Dictionary\DefaultProductOptionCode;
use App\Dictionary\ProductStatusDictionary;
use App\Entity\Common\Blameable;
use App\Entity\Common\Timestampable;
use App\Entity\Media\ProductFeaturedImage;
use App\Entity\Media\ProductGallery;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(
 *     name="products",indexes={
 *         @ORM\Index(columns={"visits"}),
 *         @ORM\Index(columns={"order_count"}),
 *         @ORM\Index(columns={"status"}),
 *         @ORM\Index(columns={"title"}),
 *         @ORM\Index(columns={"subtitle"}),
 *     }
 * )
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 *
 * @UniqueEntity(
 *     fields={"title"},
 *     message="این محصول قبلا تعریف شده است لطفا از منوی جستجو و ایجاد محصول جدید محصول خود را جستجو نمایید.",
 *     groups={"create"}
 * )
 */
class Product
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({
     *     "product.index",
     *     "product.show",
     *     "product.search",
     *     "product.create",
     *     "product.update",
     *     "order.show",
     *     "orderShipment.show",
     *     "wishlist.read",
     *     "customer.product.show",
     *     "seller.order.items.index",
     *     "seller.products.index",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.product.search",
     *     "variant.show",
     *     "admin.seller.order.items.index",
     *     "admin.seller.order.items.update_status",
     *     "cart.show",
     *     "cart.shipments",
     *     "customer.order.show",
     *     "admin.seller.order_items.index",
     *     "product.search.seller.filter",
     *     "seller.package.show",
     *     "order.items",
     *     "customer.rateAndReview.products",
     *     "customer.rateAndReview.index",
     *     "product.attribute.read",
     *     "notify.read",
     *     "wishlist.store",
     *     "customer.layout.onSaleBlocks",
     *     "return_request.show",
     *     "return_request.index",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"create", "seller.product.create"})
     * @Assert\Length(max=255, groups={"seller.product.create"})
     *
     * @Groups({
     *     "product.index",
     *     "product.show",
     *     "product.search",
     *     "order.show",
     *     "orderShipment.show",
     *     "wishlist.read",
     *     "cart.show",
     *     "cart.shipments",
     *     "inventories.index",
     *     "customer.order.show",
     *     "customer.product.show",
     *     "seller.order.items.index",
     *     "seller.products.index",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.product.search",
     *     "admin.seller.order.items.index",
     *     "admin.seller.order.items.update_status",
     *     "variant.show",
     *     "admin.seller.order_items.index",
     *     "product.search.seller.filter",
     *     "customer.rateAndReview.products",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     *     "seller.package.show",
     *     "product.better.price.read",
     *     "order.items",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     *     "carrier.inquiry.show",
     *     "product.attribute.read",
     *     "notify.read",
     *     "wishlist.store",
     *     "customer.layout.onSaleBlocks",
     *     "return_request.show",
     *     "return_request.index"
     * })
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, groups={"seller.product.create"})
     * @Groups({
     *     "product.show",
     *     "product.search",
     *     "order.show",
     *     "order.shipments",
     *     "orderShipment.show",
     *     "customer.product.show",
     *     "variant.show",
     *     "admin.seller.order.items.update_status",
     *     "product.search.seller.filter",
     *     "order.items",
     *     "customer.layout.onSaleBlocks",
     * })
     */
    private $subtitle;

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     * @Assert\Length(max=300, groups={"seller.product.create"})
     *
     * @Groups({
     *     "product.show",
     *     "product.search",
     *     "product.search.seller.filter",
     *     "customer.layout.onSaleBlocks",
     * })
     */
    private $alternativeTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({
     *     "product.show",
     *     "customer.product.show",
     *     "variant.show",
     *     "order.items",
     * })
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     * @Groups({
     *     "product.show",
     *     "customer.product.show",
     *     "variant.show",
     *     "order.items",
     * })
     */
    private $metaDescription;

    /**
     * @ORM\Column(type="string", length=2048, nullable=true)
     *
     * @Groups({
     *     "product.show",
     * })
     */
    private $additionalTitle;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({
     *     "product.index",
     *     "product.show",
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.product.show",
     *     "variant.show",
     *     "order.items",
     * })
     */
    private $isActive;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({
     *     "inventories.grid",
     *     "product.show",
     *     "order.show",
     *     "orderShipment.show",
     *     "wishlist.read",
     *     "customer.product.show",
     *     "seller.productVariant.index",
     *     "seller.product.search",
     *     "seller.products.index",
     *     "seller.variant.index",
     *     "variant.show",
     *     "product.search.seller.filter",
     *     "order.items",
     *     "notify.read",
     * })
     */
    private $isOriginal = true;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({
     *     "product.index",
     *     "product.show",
     *     "product.search",
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.product.show",
     *     "seller.products.index",
     *     "seller.product.search",
     *     "variant.show",
     *     "product.search.seller.filter",
     *     "order.items",
     *     "seller.productVariant.index",
     *     "customer.layout.onSaleBlocks",
     * })
     * @Assert\Choice(groups={"create", "update"}, callback={"App\Dictionary\ProductStatusDictionary", "toArray"})
     * @Gedmo\Versioned()
     */
    private $status;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Positive(groups={"seller.product.create"})
     * @Groups({
     *     "product.show",
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.product.show",
     *     "variant.show",
     *     "order.items",
     * })
     */
    private $length;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\NotBlank(groups={"seller.product.create"})
     * @Assert\Positive(groups={"seller.product.create"})
     * @Groups({
     *     "product.show",
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.product.show",
     *     "variant.show",
     *     "order.items",
     * })
     */
    private $width;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Positive(groups={"seller.product.create"})
     * @Groups({
     *     "product.show",
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.product.show",
     *     "variant.show",
     *     "order.items",
     * })
     */
    private $height;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Positive(groups={"seller.product.create"})
     * @Groups({
     *     "product.show",
     *     "order.show",
     *     "orderShipment.show",
     *     "customer.product.show",
     *     "variant.show",
     *     "order.items",
     * })
     */
    private $weight;

    /**
     * @ORM\Column(type="text", nullable=true, name="EAV")
     * @Groups({"product.show", "customer.product.show"})
     */
    private $EAV;

    /**
     * @var string[]
     *
     * @ORM\Column(type="json", nullable=true, name="summary_EAV")
     *
     * @Groups({"product.show", "customer.product.show"})
     */
    private $summaryEAV = [];

    /**
     * @ORM\ManyToOne(targetEntity=Brand::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(groups={"create", "update", "seller.product.create"})
     * @Groups({"product.index", "product.show", "customer.product.show", "seller.products.index", "cart.show",})
     * @MaxDepth(1)
     */
    private $brand;

    /**
     * @ORM\ManyToOne(targetEntity=ShippingCategory::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(groups={"create", "update"})
     * @Groups({"product.index", "product.show", "customer.product.show", "variant.show", })
     * @MaxDepth(1)
     */
    private $shippingCategory;

    /**
     * @ORM\Column(type="integer")
     */
    private $visits = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderCount = 0;

    /**
     * @ORM\OneToMany(targetEntity=ProductVariant::class, mappedBy="product", orphanRemoval=true)
     *
     * @Groups({
     *     "inventories.grid",
     *     "product.show",
     *     "customer.product.show",
     *     "admin.rateAndReview.index",
     *     "admin.rateAndReview.show",
     * })
     */
    private $productVariants;

    /**
     * @ORM\OneToMany(targetEntity=ProductGallery::class, cascade={"persist"},mappedBy="entity", orphanRemoval=true, fetch="EXTRA_LAZY")
     *
     * @Assert\Valid(groups={"create","update", "seller.product.create"})
     *
     * @Groups({
     *     "inventories.grid",
     *     "product.show",
     *     "customer.product.show",
     *     "seller.product.create",
     *     "order.items",
     * })
     */
    private $images;

    /**
     * @ORM\OneToMany(targetEntity=ProductFeaturedImage::class, mappedBy="entity", cascade={"persist","remove"}, fetch="EXTRA_LAZY")
     * @Assert\Valid(groups={"create","update", "seller.product.create"})
     *
     * @Groups({
     *   "inventories.grid",
     *   "product.show",
     *   "product.search",
     *   "order.show",
     *   "orderShipment.show",
     *   "wishlist.read",
     *   "customer.order.show",
     *   "customer.product.show",
     *   "cart.shipments",
     *   "seller.order.items.index",
     *   "seller.products.index",
     *   "seller.productVariant.index",
     *   "seller.variant.index",
     *   "seller.product.search",
     *   "admin.seller.order.items.index",
     *   "admin.seller.order.items.update_status",
     *   "product.search.seller.filter",
     *   "customer.rateAndReview.products",
     *   "customer.rateAndReview.index",
     *   "seller.package.show",
     *   "order.items",
     *   "notify.read",
     *   "product.index.media",
     *   "customer.layout.onSaleBlocks",
     * })
     */
    private $featuredImage;

    /**
     * @ORM\ManyToMany(targetEntity=ProductOption::class, inversedBy="products")
     *
     * @Groups({
     *     "product.index",
     *     "product.show",
     *     "customer.product.show",
     * })
     */
    private $options;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     *
     * @MaxDepth(1)
     *
     * @Assert\Expression(
     *     "this.getCategory() && this.getCategory().getChildren().count() === 0",
     *     message="Provided category must not have any children!",
     *     groups={"create", "update", "seller.product.create"}
     * )
     *
     * @Assert\Expression(
     *     "this.getCategory() && this.getCategory().getCommission() !== null",
     *     message="Provided category must have a commission fee!",
     *     groups={"create", "update", "seller.product.create"}
     * )
     *
     * @Assert\Expression(
     *     "this.getCategory() && this.getCategory().getMaxLeadTime() !== null",
     *     message="Provided category must have a lead value!",
     *     groups={"create", "update", "seller.product.create"}
     * )
     *
     * @Groups({
     *     "product.index",
     *     "product.show",
     *     "customer.product.show",
     *     "seller.products.index",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.product.search",
     *     "seller.order.items.index",
     *     "cart.show",
     *     "seller.package.show",
     * })
     */

    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=Seller::class, inversedBy="products")
     * @Groups({
     *     "customer.product.show",
     *     "product.index",
     *     "product.show",
     *     "variant.show",
     *     "seller.products.index",
     *     "seller.productVariant.index",
     * })
     */
    private $seller;

    /**
     * @ORM\OneToOne(targetEntity=Inventory::class)
     * @ORM\JoinColumn(name="buy_box_id", referencedColumnName="id")
     */
    private $buyBox;

    /**
     * @ORM\OneToMany(targetEntity=RateAndReview::class, mappedBy="product", orphanRemoval=true)
     */
    private $rateAndReviews;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({
     *     "product.show",
     * })
     */
    private $link;

    /**
     * @ORM\OneToMany(targetEntity=ProductAttribute::class, mappedBy="product", orphanRemoval=true)
     */
    private $attributes;

    /**
     * @ORM\Column(type="string", length=20,nullable=true)
     */
    private $channel;

    /**
     * @ORM\OneToMany(targetEntity=ProductIdentifier::class, mappedBy="product", orphanRemoval=true, fetch="EXTRA_LAZY")
     *
     * @Assert\Valid(groups={"create", "update", "seller.product.create"})
     */
    private $productIdentifiers;

    /**
     * @ORM\Column(type="json", nullable=true)
     *
     * @var string[]
     *
     * @Groups({
     *     "product.search",
     *     "product.search.seller.filter",
     *     "customer.layout.onSaleBlocks",
     * })
     */
    private $colors = [];

    /**
     * @ORM\Column(type="string" ,nullable=true)
     *
     * @Groups({
     *     "product.show",
     * })
     */
    private $digikalaDkp;

    /**
     * @ORM\Column(type="json", nullable=true)
     *
     * @OA\Property(type="array", @OA\Items(type="array", @OA\Items(type="string")))
     *
     * @Groups({"product.show"})
     *
     */
    private ?array $specifications = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({
     *     "product.index",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     * })
     */
    private $referencePrice;

    /**
     * Top margin of valid product price (percentage of reference price)
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({
     *     "product.index",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     * })
     */
    private $priceTopMargin;

    /**
     * Bottom margin of valid product price (percentage of reference price)
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({
     *     "product.index",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     * })
     */
    private $priceBottomMargin;

    public function __construct()
    {
        $this->productVariants    = new ArrayCollection();
        $this->images             = new ArrayCollection();
        $this->featuredImage      = new ArrayCollection();
        $this->options            = new ArrayCollection();
        $this->rateAndReviews     = new ArrayCollection();
        $this->attributes         = new ArrayCollection();
        $this->productIdentifiers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getAlternativeTitle(): ?string
    {
        return $this->alternativeTitle;
    }

    public function setAlternativeTitle(?string $alternativeTitle): self
    {
        $this->alternativeTitle = $alternativeTitle;

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

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(?float $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(?float $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(?float $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

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

    public function getAdditionalTitle(): ?string
    {
        return $this->additionalTitle;
    }

    public function setAdditionalTitle(?string $additionalTitle): self
    {
        $this->additionalTitle = $additionalTitle;

        return $this;
    }

    public function getShippingCategory(): ?ShippingCategory
    {
        return $this->shippingCategory;
    }

    public function setShippingCategory(?ShippingCategory $shippingCategory): self
    {
        $this->shippingCategory = $shippingCategory;

        return $this;
    }

    public function getVisits(): ?int
    {
        return $this->visits;
    }

    public function setVisits(int $visits): self
    {
        $this->visits = $visits;

        return $this;
    }

    public function incrementVisitCount(): self
    {
        $this->visits++;

        return $this;
    }

    public function getOrderCount(): ?int
    {
        return $this->orderCount;
    }

    public function setOrderCount(int $orderCount): self
    {
        $this->orderCount = $orderCount;

        return $this;
    }

    public function incrementOrderCount(): self
    {
        $this->orderCount++;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|ProductVariant[]
     */
    public function getProductVariants(): Collection
    {
        return $this->productVariants;
    }

    /**
     * @Groups({"customer.product.show", "seller.productVariant.index"})
     * @SerializedName("productVariants")
     *
     * @return Collection|ProductVariant[]
     *
     * @OA\Property(
     *     title="productVariants",
     *     ref=@Model(type=ProductVariant::class, groups={"customer.product.show", "seller.productVariant.index"})
     * )
     */
    public function getProductVariantsWithAtLeastOneInventory(): Collection
    {
        $productVariants = $this->productVariants
            ->filter(fn(ProductVariant $productVariant) => !$productVariant->getInventories()->isEmpty())
            ->getValues();

        return new ArrayCollection($productVariants);
    }

    /**
     * @return Collection|ProductGallery[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addProductVariant(ProductVariant $productVariant): self
    {
        if (!$this->productVariants->contains($productVariant)) {
            $this->productVariants[] = $productVariant;
            $productVariant->setProduct($this);
        }

        return $this;
    }

    public function addImage(ProductGallery $productGallery): self
    {
        if (!$this->images->contains($productGallery)) {
            $this->images[] = $productGallery;
            $productGallery->setEntity($this);
        }

        return $this;
    }

    public function removeProductVariant(ProductVariant $productVariant): self
    {
        if ($this->productVariants->contains($productVariant)) {
            $this->productVariants->removeElement($productVariant);
            // set the owning side to null (unless already changed)
            if ($productVariant->getProduct() === $this) {
                $productVariant->setProduct(null);
            }
        }

        return $this;
    }

    public function removeImage(ProductGallery $productGallery): self
    {
        if ($this->images->contains($productGallery)) {
            $this->images->removeElement($productGallery);
            // set the owning side to null (unless already changed)
            if ($productGallery->getEntity() === $this) {
                $productGallery->setEntity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProductOption[]
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(ProductOption $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
        }

        return $this;
    }

    public function removeOption(ProductOption $option): self
    {
        if ($this->options->contains($option)) {
            $this->options->removeElement($option);
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFeaturedImage(): ?ProductFeaturedImage
    {
        if (count($this->featuredImage) > 0) {
            return $this->featuredImage[0];
        }

        $default = new ProductFeaturedImage();

        $default->setAlt('No photo')->setPath('/images/products/default.png');

        return $default;
    }

    /**
     * @param mixed $image
     *
     * @return Product
     */
    public function setFeaturedImage(ProductFeaturedImage $image): self
    {
        if (!$this->featuredImage->contains($image) && $image->getEntity() !== $this) {
            $this->featuredImage = [$image];

            $image->setEntity($this);
        }

        return $this;
    }

    /**
     * @Groups({"wishlist.read", "seller.product.search"})
     */
    public function getProductPrices(): ?ArrayCollection
    {
        $minimumPriceInventory = $this->getBuyBox();

        if (is_null($minimumPriceInventory)) {
            return null;
        }

        return new ArrayCollection([
            'price'      => $minimumPriceInventory->getPrice(),
            'finalPrice' => $minimumPriceInventory->getFinalPrice(),
        ]);
    }

    public function getIsOriginal(): ?bool
    {
        return $this->isOriginal;
    }

    public function setIsOriginal(bool $isOriginal): self
    {
        $this->isOriginal = $isOriginal;

        return $this;
    }

    /**
     * @SerializedName("inventories")
     */
    public function getInventories(): ArrayCollection
    {
        $inventories = [];

        foreach ($this->getProductVariants() as $variant) {
            $variantInventories = $variant->getInventories();

            if ($variantInventories->isEmpty()) {
                continue;
            }

            array_push($inventories, ...$variantInventories->toArray());
        }

        return new ArrayCollection($inventories);
    }

    public function hasInventories(): bool
    {
        return $this->getInventories()->count() > 0;
    }

    public function hasActiveInventory(): bool
    {
        $callback = function (Inventory $inventory) {
            if ($inventory->getIsActive() && $inventory->getSellerStock() > 0) {
                return $inventory;
            }

            return false;
        };

        $inventories = collect($this->getInventories());

        return !$inventories->isEmpty() && ($inventories->first($callback) !== null);
    }

    public function getColorsOption(): ArrayCollection
    {
        $colors = collect($this->productVariants)
            ->filter(function (ProductVariant $pv) {
                $inventories = $pv->getInventories();
                foreach ($inventories as $inventory) {
                    if (
                        $inventory->isAvailable()
                    ) {
                        return true;
                    }
                }

                return false;
            })
            ->map(fn(ProductVariant $pv) => $pv->getColor())
            ->unique()
            ->values()
            ->toArray();

        return new ArrayCollection($colors);
    }

    /**
     * @Groups({"customer.product.show"})
     * @return ProductOption
     */
    public function getPrimaryOption(): ProductOption
    {
        $criteria   = Criteria::create();
        $expression = Criteria::expr();

        if (count($this->getOptions()) === 2) {
            $c = $criteria->where($expression->eq('code', DefaultProductOptionCode::COLOR));

            return $this->getOptions()->matching($c)->first();
        }

        $c = $criteria
            ->where($expression->neq('code', DefaultProductOptionCode::GUARANTEE))
            ->andWhere($expression->neq('code', DefaultProductOptionCode::COLOR));

        return $this->getOptions()->matching($c)->first();
    }

    /**
     * @Groups({"customer.product.show"})
     *
     * @return array<string>
     */
    public function getBuyBoxes(): array
    {
        $variants = $this->getProductVariantsWithAtLeastOneInventory()->getValues();

        $groupsCallback = function (ProductVariant $pv): array {
            return [$pv->getPrimaryOptionValue()->getId() => $pv->getMinimumPriceInventory()];
        };

        return collect($variants)
            ->filter(fn(ProductVariant $pv): bool => $pv->getPrimaryOptionValue() !== null)
            ->mapToGroups($groupsCallback)
            ->map
            ->sortBy(fn(Inventory $inventory) => $inventory->getFinalPrice())
            ->map
            ->first()
            ->toArray();
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(?Seller $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    /**
     * @Groups({"seller.productVariant.index", "product.show"})
     */
    public function getMaxLead(): ?int
    {
        return $this->getCategory()?->getMaxLeadTime();
    }

    public function getEAV(): ?string
    {
        return $this->EAV;
    }

    public function setEAV(?string $EAV): self
    {
        $this->EAV = $EAV;

        return $this;
    }

    public function getSummaryEAV(): ?array
    {
        return $this->summaryEAV;
    }

    public function setSummaryEAV($summaryEAV): self
    {
        $this->summaryEAV = $summaryEAV;

        return $this;
    }

    /**
     * @Groups({
     *     "product.search",
     *     "customer.layout.onSaleBlocks",
     * })
     * @SerializedName("inventory")
     */
    public function getBuyBox(): ?Inventory
    {
        return $this->buyBox;
    }

    /**
     * @Groups({"product.search.seller.filter"})
     * @SerializedName("inventory")
     */
    public function getSellerBuyBox(): ?Inventory
    {
        return collect($this->productVariants)
            ->map(fn(ProductVariant $pv) => $pv->getInventories()[0])
            ->flatten()
            ->filter(fn(Inventory $inventory) => $inventory->isAvailable())
            ->sort(fn(Inventory $x, Inventory $y) => $x->getFinalPrice() - $y->getFinalPrice())
            ->first();
    }

    public function setBuyBox(?Inventory $buyBox): self
    {
        $this->buyBox = $buyBox;

        return $this;
    }

    /**
     * @return Collection|RateAndReview[]
     */
    public function getRateAndReviews(): Collection
    {
        return $this->rateAndReviews;
    }

    public function addRateAndReview(RateAndReview $rateAndReview): self
    {
        if (!$this->rateAndReviews->contains($rateAndReview)) {
            $this->rateAndReviews[] = $rateAndReview;
            $rateAndReview->setProduct($this);
        }

        return $this;
    }

    public function removeRateAndReview(RateAndReview $rateAndReview): self
    {
        if ($this->rateAndReviews->removeElement($rateAndReview)) {
            // set the owning side to null (unless already changed)
            if ($rateAndReview->getProduct() === $this) {
                $rateAndReview->setProduct(null);
            }
        }

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return Collection|ProductAttribute[]
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function addAttribute(ProductAttribute $attribute): self
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes[] = $attribute;
            $attribute->setProduct($this);
        }

        return $this;
    }

    public function removeAttribute(ProductAttribute $attribute): self
    {
        if ($this->attributes->removeElement($attribute)) {
            // set the owning side to null (unless already changed)
            if ($attribute->getProduct() === $this) {
                $attribute->setProduct(null);
            }
        }

        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(?string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return Collection|ProductIdentifier[]
     */
    public function getProductIdentifiers(): Collection
    {
        return $this->productIdentifiers;
    }

    public function addProductIdentifier(ProductIdentifier $productIdentifier): self
    {
        if (!$this->productIdentifiers->contains($productIdentifier)) {
            $this->productIdentifiers[] = $productIdentifier;
            $productIdentifier->setProduct($this);
        }

        return $this;
    }

    public function removeProductIdentifier(ProductIdentifier $productIdentifier): self
    {
        if ($this->productIdentifiers->removeElement($productIdentifier)) {
            // set the owning side to null (unless already changed)
            if ($productIdentifier->getProduct() === $this) {
                $productIdentifier->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @Groups({"customer.product.show", "seller.package.show", "product.show", "admin.seller.order.items.index"})
     * @SerializedName("productIdentifiers")
     *
     * @OA\Property(type="array", @OA\Items(type="string"))
     *
     * @return array
     */
    public function getProductIdentifiersList(): array
    {
        return $this->getProductIdentifiers()
                    ->map(fn(ProductIdentifier $pi) => $pi->getIdentifier())
                    ->getValues();
    }

    /**
     * @return bool
     */
    public function productIdentifierConstraintIsResolved(): bool
    {
        $category = $this->getCategory();

        return $category !== null
            && !($category->productIdentifierIsRequired() && $this->getProductIdentifiers()->isEmpty());
    }

    public function isConfirmed(): bool
    {
        return ProductStatusDictionary::CONFIRMED === $this->status;
    }

    public function isUnavailable(): bool
    {
        return ProductStatusDictionary::UNAVAILABLE === $this->status;
    }

    public function getColors(): ?array
    {
        return $this->colors;
    }

    public function setColors(array $colors): self
    {
        $this->colors = $colors;

        return $this;
    }

    public function getDigikalaDkp(): ?string
    {
        return $this->digikalaDkp;
    }

    public function setDigikalaDkp(?string $digikalaDkp): self
    {
        $this->digikalaDkp = $digikalaDkp;

        return $this;
    }

    public function hasDigikalaDkp(): bool
    {
        return !is_null($this->getDigikalaDkp());
    }

    public function getSpecifications(): ?array
    {
        return $this->specifications;
    }

    public function setSpecifications(?array $specifications): self
    {
        $this->specifications = $specifications;

        return $this;
    }

    public function getReferencePrice(): ?int
    {
        return $this->referencePrice;
    }

    public function setReferencePrice(?int $referencePrice): self
    {
        $this->referencePrice = $referencePrice;

        return $this;
    }

    public function getPriceTopMargin(): ?int
    {
        return $this->priceTopMargin;
    }

    public function setPriceTopMargin(int $priceTopMargin): self
    {
        $this->priceTopMargin = $priceTopMargin;

        return $this;
    }

    public function getPriceBottomMargin(): ?int
    {
        return $this->priceBottomMargin;
    }

    public function setPriceBottomMargin(int $priceBottomMargin): self
    {
        $this->priceBottomMargin = $priceBottomMargin;

        return $this;
    }
}
