<?php

namespace App\Form;

use App\DTO\UploadMedia;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class UploadMediaType extends AbstractType
{
    private bool $isDebug;

    /**
     * UploadMediaType constructor.
     *
     * @param bool $isDebug
     */
    public function __construct(bool $isDebug = false)
    {
        $this->isDebug = $isDebug;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $constraints = [
            'maxSize'        => '8M',
            'minHeight'      => 600,
            'minWidth'       => 600,
            'allowLandscape' => false,
            'allowPortrait'  => false,
            'mimeTypes'      => ['image/jpeg'],
        ];

        // just for the sake of functional tests
        if ($this->isDebug) {
            $constraints['mimeTypes'][] = 'image/png';
        }

        if (isset($options['type']) && 'product-image' === $options['type']) {
            $constraints = array_merge($constraints, [
                'minHeight' => 1200,
                'minWidth'  => 1200,
            ]);
        }

        if ('product-content' === $options['type']) {
            unset(
                $constraints['minHeight'],
                $constraints['minWidth'],
                $constraints['allowLandscape'],
                $constraints['allowPortrait']
            );
        }

        $builder->add('imageFile', FileType::class, [
            'mapped'      => false,
            'constraints' => [
                new Image($constraints),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UploadMedia::class,
            'type'       => null,
        ]);
    }
}
