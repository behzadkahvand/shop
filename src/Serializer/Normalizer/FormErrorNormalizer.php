<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Form\FormInterface;

class FormErrorNormalizer extends AbstractCacheableNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $result = $this->convertFormToArray($object);

        if (isset($result['children'])) {
            return $result['children'];
        }

        unset($result['has_error']);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof FormInterface;
    }

    private function convertFormToArray(FormInterface $data): array
    {
        $form = $errors = [];
        $hasError = false;

        foreach ($data->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        if ($errors) {
            $form['errors'] = $errors;
            $hasError = true;
        }

        $children = [];
        foreach ($data->all() as $child) {
            if ($child instanceof FormInterface) {
                $result = $this->convertFormToArray($child);
                if ($result['has_error']) {
                    unset($result['has_error']);
                    $children[$child->getName()] = $result;
                    $hasError = true;
                }
            }
        }

        if ($children) {
            $form['children'] = $children;
        }

        $form['has_error'] = $hasError;

        return $form;
    }
}
