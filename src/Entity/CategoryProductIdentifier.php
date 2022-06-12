<?php

namespace App\Entity;

use App\Repository\CategoryProductIdentifierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="category_product_identifiers")
 * @ORM\Entity(repositoryClass=CategoryProductIdentifierRepository::class)
 */
class CategoryProductIdentifier
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotNull()
     *
     * @ORM\Column(type="boolean", options={"default"=0})
     *
     * @Groups({"categories.show", "categories.index"})
     */
    private $required = false;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\OneToOne(
     *     targetEntity=Category::class,
     *     inversedBy="categoryProductIdentifier",
     *     cascade={"persist", "remove"}
     * )
     *
     * @Assert\NotNull()
     * @Assert\Expression(
     *     "this.getCategory() && this.getCategory().getChildren().count() === 0",
     *     message="Provided category must not have any children!",
     * )
     */
    private $category;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return bool|null
     */
    public function isRequired(): ?bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     *
     * @return CategoryProductIdentifier
     */
    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     *
     * @return CategoryProductIdentifier
     */
    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
