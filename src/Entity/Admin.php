<?php

namespace App\Entity;

use App\Entity\Common\Blameable;
use App\Entity\Common\Timestampable;
use App\Repository\AdminRepository;
use App\Validator\Mobile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @ORM\Table(name="admins")
 * @UniqueEntity("email", groups={"admin.create"})
 * @ORM\Entity(repositoryClass=AdminRepository::class)
 */
class Admin implements UserInterface, PasswordAuthenticatedUserInterface, ActivableUserInterface
{
    use Blameable;
    use Timestampable;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"default", "order.show", "orderShipment.show", "order.notes.index", "order.notes.add"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email(groups={"admin.create","admin.update"})
     * @Assert\NotBlank(groups={"admin.create","admin.update"})
     * @Groups({"default"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"admin.create","admin.update"})
     * @Groups({"default","authentication", "order.show", "orderShipment.show", "order.notes.index", "order.notes.add"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"admin.create","admin.update"})
     * @Groups({"default","authentication", "order.show", "orderShipment.show", "order.notes.index", "order.notes.add"})
     */
    private $family;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"admin.create","admin.update"})
     * @Mobile(groups={"admin.create","admin.update"})
     * @Groups({"default","authentication"})
     */
    private $mobile;

    /**
     * @Assert\NotBlank(groups={"admin.create"})
     * @SerializedName("password")
     */
    private $plainPassword;

    /**
     * @ORM\OneToMany(targetEntity=OrderStatusLog::class, mappedBy="user")
     */
    private $orderStatusLogs;

    /**
     * @ORM\OneToMany(targetEntity=OrderShipmentStatusLog::class, mappedBy="user")
     */
    private $orderShipmentStatusLogs;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @OA\Property(type="array", @OA\Items(type="string"))
     *
     * @Groups({"default","authentication"})
     *
     */
    private ?array $permissions = [];

    /**
     * @ORM\OneToMany(targetEntity=OrderItemLog::class, mappedBy="user")
     */
    private $orderItemLogs;

    /**
     * @ORM\Column(type="boolean", options={"default" : true})
     *
     * @Groups({
     *     "default",
     * })
     */
    private $isActive;

    public function __construct()
    {
        $this->orderStatusLogs = new ArrayCollection();
        $this->orderShipmentStatusLogs = new ArrayCollection();
        $this->orderItemLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_ADMIN';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFamily(): ?string
    {
        return $this->family;
    }

    public function setFamily(?string $family): self
    {
        $this->family = $family;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     * @return Admin
     */
    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * @return Collection|OrderStatusLog[]
     */
    public function getOrderStatusLogs(): Collection
    {
        return $this->orderStatusLogs;
    }

    public function addOrderStatusLog(OrderStatusLog $orderStatusLog): self
    {
        if (!$this->orderStatusLogs->contains($orderStatusLog)) {
            $this->orderStatusLogs[] = $orderStatusLog;
            $orderStatusLog->setUser($this);
        }

        return $this;
    }

    public function removeOrderStatusLog(OrderStatusLog $orderStatusLog): self
    {
        if ($this->orderStatusLogs->contains($orderStatusLog)) {
            $this->orderStatusLogs->removeElement($orderStatusLog);
            // set the owning side to null (unless already changed)
            if ($orderStatusLog->getUser() === $this) {
                $orderStatusLog->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|OrderShipmentStatusLog[]
     */
    public function getOrderShipmentStatusLogs(): Collection
    {
        return $this->orderShipmentStatusLogs;
    }

    public function addOrderShipmentStatusLog(OrderShipmentStatusLog $orderShipmentStatusLog): self
    {
        if (!$this->orderShipmentStatusLogs->contains($orderShipmentStatusLog)) {
            $this->orderShipmentStatusLogs[] = $orderShipmentStatusLog;
            $orderShipmentStatusLog->setUser($this);
        }

        return $this;
    }

    public function removeOrderShipmentStatusLog(OrderShipmentStatusLog $orderShipmentStatusLog): self
    {
        if ($this->orderShipmentStatusLogs->contains($orderShipmentStatusLog)) {
            $this->orderShipmentStatusLogs->removeElement($orderShipmentStatusLog);
            // set the owning side to null (unless already changed)
            if ($orderShipmentStatusLog->getUser() === $this) {
                $orderShipmentStatusLog->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|OrderItemLog[]
     */
    public function getOrderItemLogs(): Collection
    {
        return $this->orderItemLogs;
    }

    public function addOrderItemLog(OrderItemLog $orderItemLog): self
    {
        if (!$this->orderItemLogs->contains($orderItemLog)) {
            $this->orderItemLogs[] = $orderItemLog;
            $orderItemLog->setUser($this);
        }

        return $this;
    }

    public function removeOrderItemLog(OrderItemLog $orderItemLog): self
    {
        if ($this->orderItemLogs->removeElement($orderItemLog)) {
            // set the owning side to null (unless already changed)
            if ($orderItemLog->getUser() === $this) {
                $orderItemLog->setUser(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getFullName(): string
    {
        return $this->getName() . ' ' . $this->getFamily();
    }

    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    public function setPermissions(?array $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive($isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->getIsActive();
    }
}
