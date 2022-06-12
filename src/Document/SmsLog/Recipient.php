<?php

namespace App\Document\SmsLog;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class Recipient
{
    /**
     * @MongoDB\Field(name="mobile", type="string")
     */
    protected $mobile;

    /**
     * @MongoDB\Field(name="name", type="string")
     */
    protected $name;

    /**
     * @MongoDB\Field(name="userType", type="string")
     */
    protected $userType;

    /**
     * @MongoDB\Field(name="userId", type="string")
     */
    protected $userId;

    public function setMobile($mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setUserType($userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function setUserId($userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
