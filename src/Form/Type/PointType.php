<?php

namespace App\Form\Type;

use LongitudeOne\Spatial\PHP\Types\AbstractPoint;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class PointType extends AbstractType implements DataTransformerInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('long', null, [
            'constraints' => [
                new Range([
                    'min'            => -180,
                    'max'            => 180,
                    'invalidMessage' => "It's not a valid longitude."
                ])
            ],
        ]);
        $builder->add('lat', null, [
            'constraints' => [
                new Range([
                    'min'            => -90,
                    'max'            => 90,
                    'invalidMessage' => "It's not a valid latitude."
                ])
            ],
        ]);
        $builder->addModelTransformer($this);
    }

    public function transform($value): array
    {
        if (empty($value)) {
            return [];
        }

        $this->CheckPoint($value);

        return [
            'long' => $value->getLongitude(),
            'lat'  => $value->getLatitude(),
        ];
    }

    public function reverseTransform($value): ?Point
    {
        if (empty($value)) {
            return null;
        }

        $this->CheckPoint($value);

        /** @var AbstractPoint $value */
        return new Point($value['long'], $value['lat']);
    }

    /**
     * @param $value
     */
    protected function checkPoint($value): void
    {
        if (!is_array($value) || 2 !== count($value) || !isset($value['lat']) || !isset($value['long'])) {
            throw new TransformationFailedException();
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'invalid_message' => 'This value is not a valid coordinate.'
        ]);
    }
}
