<?php

namespace App\Entity;

use App\Entity\Common\CodeAwareInterface;
use App\Entity\Common\Timestampable;
use App\Repository\ProvinceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LongitudeOne\Spatial\PHP\Types\Geometry\MultiPolygon;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="provinces")
 * @ORM\Entity(repositoryClass=ProvinceRepository::class)
 */
class Province implements CodeAwareInterface
{
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     "default",
     *     "customer.read",
     *     "province.details",
     *     "order.show",
     *     "customer.legal.account.store",
     *     "customer.legal.account.show",
     *     "order.legal.account.store",
     * })
     */
    private $id;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Groups({"default", "province.details"})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({
     *     "default",
     *     "orderShipment.index",
     *     "order.show",
     *     "orderShipment.show",
     *     "product.better.price.read",
     *     "carrier.inquiry.show",
     *     "customer.legal.account.store",
     *     "customer.legal.account.show",
     *     "customer.read",
     *     "order.legal.account.store",
     *     "province.details",
     *     "orderShipment.show.driver",
     * })
     */
    private $name;

    /**
     * @ORM\Column(type="multipolygon", length=255, nullable=true)
     */
    private $coordinates;

    /**
     * @ORM\OneToMany(targetEntity=City::class, mappedBy="province")
     */
    private $cities;

    public function __construct()
    {
        $this->cities             = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCoordinates(): ?MultiPolygon
    {
        return $this->coordinates;
    }

    public function setCoordinates(MultiPolygon $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    /**
     * @return Collection|City[]
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): self
    {
        if (!$this->cities->contains($city)) {
            $this->cities[] = $city;
            $city->setProvince($this);
        }

        return $this;
    }

    public function removeCity(City $city): self
    {
        if ($this->cities->contains($city)) {
            $this->cities->removeElement($city);
            // set the owning side to null (unless already changed)
            if ($city->getProvince() === $this) {
                $city->setProvince(null);
            }
        }

        return $this;
    }
}
