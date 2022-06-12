<?php

namespace App\Service\Utils\Error;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

final class FormErrorToValidationViolationTransformer
{
    /**
     * @var ErrorExtractor
     */
    private $errorExtractor;

    /**
     * FormErrorToValidationViolationTransformer constructor.
     *
     * @param ErrorExtractor $errorExtractor
     */
    public function __construct(ErrorExtractor $errorExtractor)
    {
        $this->errorExtractor = $errorExtractor;
    }

	/**
     *
     * @param FormInterface $form
     *
	 * @return ConstraintViolationList
     */
    public function transform(FormInterface $form)
    {
        $violations = [];
        foreach ($this->errorExtractor->extract($form) as $field => $message) {
            $violations[] = new ConstraintViolation($message, null, [], false === strpos($field, '.'), $field, '');
        }

        return new ConstraintViolationList($violations);
    }
}
