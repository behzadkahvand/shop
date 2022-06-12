<?php

namespace App\Service\Utils\Error;

/**
 * Class ErrorExtractor
 */
class ErrorExtractor
{
    /**
     * @var iterable|ErrorExtractorInterface
     */
    private $extractors;

    /**
     * ErrorExtractor constructor.
     *
     * @param iterable $extractors
     */
    public function __construct(iterable $extractors)
    {
        $this->extractors = $extractors;
    }

    /**
     * @param $errors
     *
     * @return iterable
     */
    public function extract($errors): array
    {
        $errorExtractor = null;

        foreach ($this->extractors as $extractor) {
            if ($extractor->support($errors)) {
                $errorExtractor = $extractor;
                break;
            }
        }

        if (null !== $errorExtractor) {
            $result = $errorExtractor->extract($errors);
        } elseif (is_iterable($errors)) {
            $result = $errors;
        } else {
            $result = $errors;
        }

        return is_array($result) ? $result : iterator_to_array($result);
    }
}
