<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Tree\Entity\MappedSuperclass\AbstractClosure;

/**
 * @ORM\Table(name="category_closures")
 * @ORM\Entity()
 */
class CategoryClosure extends AbstractClosure
{
    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="ancestor", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $ancestor;

    /**
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumn(name="descendant", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $descendant;
}
