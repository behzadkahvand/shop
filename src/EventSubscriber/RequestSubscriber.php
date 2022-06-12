<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestSubscriber implements EventSubscriberInterface
{
    public function transformJSONRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $content = $request->getContent();

        if (empty($content) === false && $request->getContentType() === 'json') {
            $data = json_decode($this->getNormalizedContent($content), true);
            if (JSON_ERROR_NONE === json_last_error() && $data !== null) {
                $request->request->replace($data);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'transformJSONRequest',
        ];
    }

    /**
     * @return string
     */
    private function getNormalizedContent(string $content)
    {
        $englishNumbers = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9,];
        $nonEnglishNumbers = [
            "۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹", "٠", "١", "٢", "٣", "٤", "٥", "٦", "٧", "٨", "٩",
        ];

        $nonPersianChars = ["ى", "ي", "ك", "إ", "أ", "ٱ", "ة", "ؤ", "ء"];
        $persianChars    = ["ی", "ی", "ک", "ا", "ا", "ﺍ", "ه", "و", ""];

        return str_replace($nonPersianChars, $persianChars, str_replace($nonEnglishNumbers, $englishNumbers, $content));
    }
}
