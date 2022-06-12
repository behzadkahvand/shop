<?php

namespace App\Service\ExceptionHandler\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @Annotation
 *
 * @Target("CLASS")
 */
class Metadata
{
    public bool $isVisibleForUsers;
    public int $statusCode;
    public string $title;
    public array $detail;

    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $options = $resolver->resolve($options);

        $this->isVisibleForUsers = $options['isVisibleForUsers'];
        $this->statusCode        = $options['statusCode'];
        $this->title             = $options['title'];
        $this->detail            = $options['detail'];
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'isVisibleForUsers' => false,
            'statusCode'        => 500,
            'title'             => 'Internal Server Error',
            'detail'            => function (OptionsResolver $details) {
                $details->setDefaults([
                    'message'     => 'An error occurred!',
                    'translation' => function (OptionsResolver $translations) {
                        $translations->setDefault('key', null);
                        $translations->setAllowedTypes('key', ['string', 'null']);

                        $translations->setDefault('dataMethod', '');
                        $translations->setAllowedTypes('dataMethod', 'string');
                    },
                ]);

                $details->setAllowedTypes('message', 'string');
            },
        ]);

        $resolver->setAllowedTypes('isVisibleForUsers', 'bool');
        $resolver->setAllowedTypes('statusCode', 'int');
        $resolver->setAllowedValues('statusCode', array_keys(Response::$statusTexts));
        $resolver->setAllowedTypes('title', 'string');
        $resolver->setAllowedTypes('detail', 'array');
    }
}
