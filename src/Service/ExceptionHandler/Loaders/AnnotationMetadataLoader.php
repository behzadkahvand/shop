<?php

namespace App\Service\ExceptionHandler\Loaders;

use App\Service\ExceptionHandler\Annotations\Metadata;
use App\Service\ExceptionHandler\ThrowableMetadata;
use Doctrine\Common\Annotations\Reader;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class AnnotationMetadataLoader implements MetadataLoaderInterface
{
    private Reader $reader;

    private TranslatorInterface $translator;

    public function __construct(Reader $reader, TranslatorInterface $translator)
    {
        $this->reader     = $reader;
        $this->translator = $translator;
    }

    public function load(Throwable $throwable): ThrowableMetadata
    {
        $reflection = new \ReflectionClass($throwable);
        $mapping    = $this->reader->getClassAnnotation($reflection, Metadata::class);

        return new ThrowableMetadata(
            $mapping->isVisibleForUsers,
            $mapping->statusCode,
            $this->getDetail($mapping, $throwable)
        );
    }

    public function supports(Throwable $throwable): bool
    {
        $reflection = new \ReflectionClass($throwable);

        return !is_null($this->reader->getClassAnnotation($reflection, Metadata::class));
    }

    public static function getPriority(): int
    {
        return 50;
    }

    private function getDetail(Metadata $mapping, Throwable $throwable): string
    {
        $id = $mapping->detail['translation']['key'] ?? false;

        if (!$id) {
            return $mapping->detail['message'];
        }

        $method     = $mapping->detail['translation']['dataMethod'];
        $parameters = $method && method_exists($throwable, $method) ? $throwable->$method() : [];

        return $this->translator->trans($id, $parameters, 'exceptions', 'fa');
    }
}
