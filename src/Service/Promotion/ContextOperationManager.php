<?php

namespace App\Service\Promotion;

class ContextOperationManager
{
    public function addErrorMessage(&$context, $errorMessage)
    {
        if (!isset($context['error_messages'])) {
            $context['error_messages'] = [];
        }

        $context['error_messages'][] = $errorMessage;
    }

    public function getFirstErrorMessage(array $context): ?string
    {
        if (
            !isset($context['error_messages']) ||
            !is_array($context['error_messages']) ||
            empty($context['error_messages'])
        ) {
            return null;
        }

        return array_pop($context['error_messages']);
    }
}
