<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductIdentifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductIdentifierType extends AbstractType implements DataTransformerInterface
{
    private EntityManagerInterface $em;

    private ?Product $product;

    private static array $resolvedIdentifiers = [];

    /**
     * ProductIdentifierType constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em      = $em;
        $this->product = null;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->product = $options['product'] ?? null;

        $builder->addModelTransformer($this);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound' => false,
            'product'  => null,
        ]);

        $resolver->setAllowedTypes('product', ['null', Product::class]);
    }

    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        return $value instanceof ProductIdentifier ? $value->getIdentifier() : $value;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            $this->throwTransformationFailedException(
                'Invalid identifier given. expected string got ' . get_debug_type($value)
            );
        }

        if ('' === trim($value)) {
            $this->throwTransformationFailedException((new NotBlank())->message);
        }

        if (null !== $this->product) {
            foreach ($this->product->getProductIdentifiers() as $productIdentifier) {
                if ($productIdentifier->getIdentifier() === $value) {
                    if ($this->isResolved($value, $this->product) || !$productIdentifier->getId()) {
                        $this->throwTransformationFailedException("Product identifier {$value} is already used!");
                    }

                    $this->markAsResolved($value, $this->product);

                    return $productIdentifier;
                }
            }
        }

        $productIdentifier = new ProductIdentifier($value, $this->product);

        $this->em->persist($productIdentifier);

        $this->markAsResolved($value, $this->product);

        return $productIdentifier;
    }

    /**
     * @param string $message
     */
    private function throwTransformationFailedException(string $message): void
    {
        $e = new TransformationFailedException();
        $e->setInvalidMessage($message);

        throw $e;
    }

    /**
     * @param string $productIdentifier
     * @param Product|null $product
     *
     * @return bool
     */
    private function isResolved(string $productIdentifier, ?Product $product): bool
    {
        return isset(self::$resolvedIdentifiers[$product ? $product->getId() : 'not_persisted'][$productIdentifier]);
    }

    /**
     * @param string $productIdentifier
     * @param Product|null $product
     */
    private function markAsResolved(string $productIdentifier, ?Product $product): void
    {
        $index = $product ? $product->getId() : 'not_persisted';

        self::$resolvedIdentifiers[$index][$productIdentifier] = true;
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        if (isset($this->product)) {
            unset($this->product);
        }
    }
}
