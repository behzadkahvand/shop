<?php

namespace App\DTO\Seller;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class SellerChangePassword
{
    /**
     * @SecurityAssert\UserPassword(
     *     groups={"seller.update.password"},
     *     message = "Wrong value for your current password"
     * )
     */
    protected $oldPassword;

    /**
     * @Assert\NotBlank(groups={"seller.update.password"})
     * @Assert\Length(min="6", groups={"seller.update.password"})
     */
    protected $newPassword;

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword($oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword($newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }
}
