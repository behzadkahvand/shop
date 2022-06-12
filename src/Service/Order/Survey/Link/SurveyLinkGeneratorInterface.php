<?php

namespace App\Service\Order\Survey\Link;

/**
 * Interface SurveyLinkGeneratorInterface
 */
interface SurveyLinkGeneratorInterface
{
    /**
     * @param string $orderIdentifier
     *
     * @return string
     */
    public function generateLink(string $orderIdentifier): string;
}
