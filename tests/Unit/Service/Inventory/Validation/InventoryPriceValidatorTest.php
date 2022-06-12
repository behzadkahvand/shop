<?php

namespace App\Tests\Unit\Service\Inventory\Validation;

use App\Entity\Inventory;
use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Exceptions\Inventory\InventoryPriceRuleViolationException;
use App\Service\Inventory\Validation\InventoryPriceValidator;
use App\Tests\Unit\BaseUnitTestCase;
use Mockery;
use Symfony\Contracts\Translation\TranslatorInterface;

class InventoryPriceValidatorTest extends BaseUnitTestCase
{
    public function testShouldDoNothingIfProductDoesNotHaveReferencePrice(): void
    {
        $translator = Mockery::mock(TranslatorInterface::class);
        $product = new Product();
        $inventory = new Inventory();
        $variant = new ProductVariant();

        $variant->setProduct($product);
        $inventory->setVariant($variant);

        $sut = new InventoryPriceValidator($translator);

        self::expectNotToPerformAssertions();

        $sut->validate($inventory);
    }

    public function testShouldDoNothingIfInventoryPriceIsInValidRange(): void
    {
        $translator = Mockery::mock(TranslatorInterface::class);
        $product = new Product();
        $inventory = new Inventory();
        $variant = new ProductVariant();

        $variant->setProduct($product);
        $inventory->setVariant($variant);

        $product->setReferencePrice(1000);
        $product->setPriceTopMargin(20);
        $product->setPriceBottomMargin(30);

        $inventory->setPrice(1100);

        $sut = new InventoryPriceValidator($translator);

        self::expectNotToPerformAssertions();

        $sut->validate($inventory);
    }

    public function testShouldThrowExceptionIfInventoryPriceIsGreaterThanProductTopPriceMargin(): void
    {
        $product = new Product();
        $inventory = new Inventory();
        $variant = new ProductVariant();
        $translator = Mockery::mock(TranslatorInterface::class);

        $variant->setProduct($product);
        $inventory->setVariant($variant);

        $product->setReferencePrice(1000);
        $product->setPriceTopMargin(20);
        $product->setPriceBottomMargin(30);

        $inventory->setPrice(1300);

        $translator->expects('trans')->with('inventory_reference_price_rule_violated', [], 'exceptions');

        $sut = new InventoryPriceValidator($translator);

        self::expectException(InventoryPriceRuleViolationException::class);

        $sut->validate($inventory);
    }

    public function testShouldThrowExceptionIfInventoryPriceIsLessThanProductBottomPriceMargin(): void
    {
        $product = new Product();
        $inventory = new Inventory();
        $variant = new ProductVariant();
        $translator = Mockery::mock(TranslatorInterface::class);

        $variant->setProduct($product);
        $inventory->setVariant($variant);

        $product->setReferencePrice(1000);
        $product->setPriceTopMargin(20);
        $product->setPriceBottomMargin(30);

        $inventory->setPrice(600);

        $translator->expects('trans')->with('inventory_reference_price_rule_violated', [], 'exceptions');

        $sut = new InventoryPriceValidator($translator);

        self::expectException(InventoryPriceRuleViolationException::class);

        $sut->validate($inventory);
    }
}
