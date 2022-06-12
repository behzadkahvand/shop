<?php

namespace App\Service\File\Uploader;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private SluggerInterface $slugger;

    private string $publicDirectory;

    public function __construct(string $publicDirectory, SluggerInterface $slugger)
    {
        $this->publicDirectory = $publicDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file, ?string $directory = null): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid('', true) . '.' . $file->getClientOriginalExtension();
        $destinationDirectory = $this->getDestinationDirectory($directory);

        try {
            $file->move($destinationDirectory, $fileName);
        } catch (FileException $e) {
            throw $e;
        }

        return $destinationDirectory . DIRECTORY_SEPARATOR . $fileName;
    }

    private function getDestinationDirectory(?string $directory): string
    {
        if ($directory !== null) {
            return $this->publicDirectory . DIRECTORY_SEPARATOR . $directory;
        }

        return $this->publicDirectory;
    }
}
