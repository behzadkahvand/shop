<?php

namespace App\Entity\Common;

interface CodeAwareInterface
{
    /**
     * This code must be unique in the entity.
     * It will be used as slug or internal purposes.
     *
     * @return string
     */
    public function getCode(): ?string;
}
