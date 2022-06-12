<?php

namespace App\Entity;

use App\Entity\Common\Blameable;
use App\Entity\Common\Timestampable;
use App\Entity\Media\CategoryImage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Gedmo\Tree(type="closure")
 * @Gedmo\TreeClosure(class="App\Entity\CategoryClosure")
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 *
 * @UniqueEntity(
 *     fields={"code"},
 *     errorPath="code",
 *     message="This slug is already used.",
 *     groups={"categories.store", "categories.update"}
 * )
 * @UniqueEntity(
 *     fields={"title"},
 *     errorPath="title",
 *     message="This title is already used.",
 *     groups={"categories.store", "categories.update"}
 * )
 * @UniqueEntity(
 *     fields={"subtitle"},
 *     errorPath="subtitle",
 *     message="This subtitle is already used.",
 *     groups={"categories.store", "categories.update"}
 * )
 */
class Category
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "categories.index",
     *     "categories.show",
     *     "categories.store",
     *     "categories.update",
     *     "category.delivery.index",
     *     "category.delivery.show",
     *     "category.delivery.store",
     *     "category.delivery.update",
     *     "category.commission.index",
     *     "category.commission.store",
     *     "category.commission.update",
     *     "product.show",
     *     "customer.product.show",
     *     "seller.product.categories_index",
     *     "category.product_options.store",
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     *     "seller.category_product.search",
     *     "category.attribute.group.read",
     *     "category.discount.index",
     *     "category.attribute.group.read",
     *     "category.attribute.read",
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     *     "customer.show.root.categories",
     *     "campaignCommission.show"
     * })
     */
    private $id;

    /**
     * @Gedmo\TreeParent()
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="children", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @MaxDepth(1)
     *
     * @Groups({"categories.show"})
     */
    private $parent;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(groups={"categories.store", "categories.update"})
     *
     * @Groups({
     *     "categories.index",
     *     "categories.show",
     *     "customer.product.show",
     *     "category.product_options.store",
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     *     "seller.category_product.search",
     *     "category.discount.index",
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     * })
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity=CategoryImage::class, mappedBy="entity", cascade={"persist","remove"}, fetch="EXTRA_LAZY")
     * @Assert\Valid(groups={"categories.store"})
     * @Groups({"inventories.grid", "categories.show"})
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Assert\NotBlank(groups={"categories.store", "categories.update"})
     * @Assert\NotNull(groups={"categories.store", "categories.update"})
     *
     * @Groups({
     *     "categories.index",
     *     "categories.show",
     *     "categories.store",
     *     "categories.update",
     *     "category.delivery.index",
     *     "category.delivery.show",
     *     "category.delivery.store",
     *     "category.delivery.update",
     *     "category.commission.index",
     *     "category.commission.store",
     *     "category.commission.update",
     *     "product.index",
     *     "product.show",
     *     "customer.product.show",
     *     "seller.order.items.index",
     *     "seller.productVariant.index",
     *     "seller.variant.index",
     *     "seller.product.search",
     *     "seller.products.index",
     *     "seller.product.categories_index",
     *     "cart.show",
     *     "seller.package.show",
     *     "category.product_options.store",
     *     "category.product_options.values.add",
     *     "category.product_options.values.remove",
     *     "category.product_options.index",
     *     "category.product_options.show",
     *     "seller.category_product.search",
     *     "category.discount.index",
     *     "category.attribute.group.read",
     *     "category.attribute.group.read",
     *     "category.attribute.read",
     *     "seo.selected_filters.store.show",
     *     "seo.selected_filters.store",
     *     "category_brand_seller_product_option.index",
     *     "category_brand_seller_product_option.show",
     *     "customer.show.root.categories",
     *     "campaignCommission.show"
     * })
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(groups={"categories.store", "categories.update"}, allowNull=true)
     * @Groups({"categories.index", "categories.show", "customer.product.show"})
     */
    private ?string $pageTitle;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Assert\NotBlank(groups={"categories.store", "categories.update"})
     * @Assert\NotNull(groups={"categories.store", "categories.update"})
     *
     * @Groups({"categories.index", "categories.show", "customer.product.show"})
     */
    private $subtitle;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\NotBlank(groups={"categories.store", "categories.update"})
     *
     * @Groups({"categories.show"})
     */
    private $level;

    /**
     * Use below annotation on `json` fields to prevent swagger error {"Notice: Undefined variable: class"}
     *
     * @var string[]
     *
     * @ORM\Column(type="json")
     *
     * @Groups({"categories.show"})
     */
    private $configurations = [];

    /**
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="parent", orphanRemoval=true, cascade={"remove"}, fetch="EXTRA_LAZY")
     * @MaxDepth(10)
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     *
     * @Groups({"categories.show"})
     */
    private $children;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="category", orphanRemoval=true)
     */
    private $products;

    /**
     * @ORM\Column(type="float", nullable=true)
     *
     * @Groups({
     *     "category.commission.index",
     *     "category.commission.store",
     *     "category.commission.update",
     *     "seller.productVariant.index",
     *     "categories.index",
     *     "categories.show"
     * })
     */
    private $commission;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({
     *     "categories.index",
     *     "categories.show"
     * })
     */
    private $maxLeadTime;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NotBlank(groups={"categories.store", "categories.update"}, allowNull=true)
     *
     * @Groups({"categories.show"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     *
     * @Assert\NotBlank(groups={"categories.store", "categories.update"}, allowNull=true)
     *
     * @Groups({"categories.show"})
     */
    private $metaDescription;

    /**
     * @ORM\OneToMany(targetEntity=CategoryProductOption::class, mappedBy="category")
     *
     * @Groups({
     *     "category.product_options.store",
     *     "category.product_options.index",
     *     "category.product_options.show",
     * })
     */
    private $categoryProductOptions;

    /**
     * @ORM\OneToOne(targetEntity=CategoryDiscountRange::class, mappedBy="category", cascade={"persist", "remove"})
     */
    private $discountRange;

    /**
     * @ORM\OneToMany(targetEntity=CategoryAttributeGroup::class, mappedBy="category", orphanRemoval=true)
     */
    private $categoryAttributeGroups;

    /**
     * @ORM\OneToOne(
     *     targetEntity=CategoryProductIdentifier::class,
     *     mappedBy="category",
     *     cascade={"persist", "remove"}
     * )
     *
     * @Assert\NotBlank(groups={"categories.store", "categories.update"}, allowNull=true)
     *
     * @Groups({"categories.show", "categories.index"})
     */
    private $categoryProductIdentifier;

    public function __construct()
    {
        $this->children                = new ArrayCollection();
        $this->products                = new ArrayCollection();
        $this->image                   = new ArrayCollection();
        $this->categoryProductOptions  = new ArrayCollection();
        $this->categoryAttributeGroups = new ArrayCollection();
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

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

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

    public function getConfigurations(): ?array
    {
        return $this->configurations;
    }

    public function setConfigurations($configurations): self
    {
        $this->configurations = $configurations;

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
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }

    public function getCommission(): ?float
    {
        return $this->commission;
    }

    public function setCommission(?float $commission): self
    {
        $this->commission = $commission;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImage(): ?CategoryImage
    {
        $criteria = Criteria::create()
                            ->orderBy(['id' => Criteria::DESC])
                            ->setFirstResult(0)
                            ->setMaxResults(1);

        return $this->image->matching($criteria)->first() ?: null;
    }

    /**
     * @param CategoryImage $image
     *
     * @return Category
     */
    public function setImage(CategoryImage $image): self
    {
        if (!$this->image->contains($image) && $image->getEntity() !== $this) {
            $this->image = [$image];
            $image->setEntity($this);
        }

        return $this;
    }

    public function getRootCategory(): self
    {
        if (null === $this->getParent()) {
            return $this;
        }

        return $this->getParent()->getRootCategory();
    }

    /**
     * @Groups({"categories.index", "seller.product.categories_index"})
     */
    public function isLeaf(): bool
    {
        return $this->getChildren()->count() === 0;
    }

    public function hasProducts(): bool
    {
        return $this->getProducts()->count() > 0;
    }

    public function getPageTitle(): ?string
    {
        return $this->pageTitle;
    }

    public function setPageTitle($pageTitle): self
    {
        $this->pageTitle = $pageTitle;

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
     * @return Collection|CategoryProductOption[]
     */
    public function getCategoryProductOptions(): Collection
    {
        return $this->categoryProductOptions;
    }

    public function addCategoryProductOption(CategoryProductOption $categoryProductOption): self
    {
        if (!$this->categoryProductOptions->contains($categoryProductOption)) {
            $this->categoryProductOptions[] = $categoryProductOption;
            $categoryProductOption->setCategory($this);
        }

        return $this;
    }

    public function removeCategoryProductOption(CategoryProductOption $categoryProductOption): self
    {
        if ($this->categoryProductOptions->removeElement($categoryProductOption)) {
            // set the owning side to null (unless already changed)
            if ($categoryProductOption->getCategory() === $this) {
                $categoryProductOption->setCategory(null);
            }
        }

        return $this;
    }

    public function getOptions(): ArrayCollection
    {
        $inventories = collect($this->categoryProductOptions)
            ->map(fn($cpo) => $cpo->getProductOption())
            ->toArray();

        return new ArrayCollection($inventories);
    }

    public function getDiscountRange(): ?CategoryDiscountRange
    {
        return $this->discountRange;
    }

    public function setDiscountRange(CategoryDiscountRange $discountRange): self
    {
        // set the owning side of the relation if necessary
        if ($discountRange->getCategory() !== $this) {
            $discountRange->setCategory($this);
        }

        $this->discountRange = $discountRange;

        return $this;
    }

    /**
     * @return Collection|AttributeGroup[]
     */
    public function getCategoryAttributeGroups(): Collection
    {
        return $this->categoryAttributeGroups;
    }

    public function addCategoryAttributeGroup(CategoryAttributeGroup $categoryAttributeGroup): self
    {
        if (!$this->categoryAttributeGroups->contains($categoryAttributeGroup)) {
            $this->categoryAttributeGroups[] = $categoryAttributeGroup;
            $categoryAttributeGroup->setCategory($this);
        }

        return $this;
    }

    public function removeCategoryAttributeGroup(CategoryAttributeGroup $categoryAttributeGroup): self
    {
        if ($this->categoryAttributeGroups->removeElement($categoryAttributeGroup)) {
            // set the owning side to null (unless already changed)
            if ($categoryAttributeGroup->getCategory() === $this) {
                $categoryAttributeGroup->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return CategoryProductIdentifier|null
     */
    public function getCategoryProductIdentifier(): ?CategoryProductIdentifier
    {
        return $this->categoryProductIdentifier;
    }

    /**
     * @param CategoryProductIdentifier $categoryProductIdentifier
     *
     * @return $this
     */
    public function setCategoryProductIdentifier(CategoryProductIdentifier $categoryProductIdentifier): self
    {
        // set the owning side of the relation if necessary
        if ($categoryProductIdentifier->getCategory() !== $this) {
            $categoryProductIdentifier->setCategory($this);
        }

        $this->categoryProductIdentifier = $categoryProductIdentifier;

        return $this;
    }

    /**
     * @return bool
     */
    public function productIdentifierIsRequired(): bool
    {
        if (null === $categoryProductIdentifier = $this->getCategoryProductIdentifier()) {
            return false;
        }

        return $categoryProductIdentifier->isRequired();
    }

    public function getMaxLeadTime(): ?int
    {
        return $this->maxLeadTime;
    }

    public function setMaxLeadTime(int $maxLeadTime): self
    {
        $this->maxLeadTime = $maxLeadTime;

        return $this;
    }
}
