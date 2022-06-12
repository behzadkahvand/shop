<?php

namespace App\DTO;

use App\Entity\Media\Media;
use Symfony\Component\Serializer\Annotation\Groups;

class UploadMedia
{
    /**
     * @var string|null
     *
     * @Groups("image.create")
     */
    private $imageFileName;

    /**
     * @var Media|null
     *
     * @Groups({"image.create"})
     */
    private $media;

    /**
     * @return string
     */
    public function getImageFileName(): ?string
    {
        return $this->imageFileName;
    }

    /**
     * @param mixed $imageFileName
     * @return UploadMedia
     */
    public function setImageFileName(string $imageFileName): UploadMedia
    {
        $this->imageFileName = $imageFileName;
        return $this;
    }

    /**
     * @return Media|null
     */
    public function getMedia(): ?Media
    {
        return $this->media;
    }

    /**
     * @param Media|null $media
     */
    public function setMedia(?Media $media)
    {
        $this->media = $media;
    }
}
