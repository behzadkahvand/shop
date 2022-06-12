<?php

namespace App\Service\Utils\Error\Adapters;

use App\Service\Utils\Error\ErrorExtractorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormErrorExtractor
 */
final class FormErrorExtractor implements ErrorExtractorInterface
{
    /**
     * @inheritDoc
     */
    public function support($errors): bool
    {
        return $errors instanceof FormInterface;
    }

    /**
     * @inheritDoc
     */
    public function extract($errors): iterable
    {
        /** @var FormInterface $errors */
        foreach ($errors->getErrors(true, true) as $error) {
            if ($error->getOrigin()->getParent() === null) {
                continue;
            }

            $name    = $this->getNamePrefix($error) . $error->getOrigin()->getName();
            $message = $error->getMessage();

            yield $name => $message;
        }
    }

    private function getNamePrefix(FormError $error): string
    {
        $parents = [];
        $form    = $error->getOrigin();

        while ($parent = $form->getParent()) {
            $parents[] = $parent->getName();
            $form      = $parent;
        }

        if (empty($parents)) {
            return '';
        }

        $parents   = array_reverse($parents);
        $parents[] = '';

        return implode('.', $parents);
    }
}
