<?php

namespace App\Tests\Unit;

use Mockery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Form\Test\TypeTestCase;

class BaseTypeTestCase extends TypeTestCase
{
    /*
    * phpunit is not still fully compatible with php 8, and using phpunit/MockObject causes tests
    * to fail, this class is created to skip setUp method of parent class(TypeTestCase)
    * and create dispatcher with Mockery instead of MockObject
    */
    protected function setUp(): void
    {
        FormIntegrationTestCase::setUp();

        $this->dispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->builder = new FormBuilder('', null, $this->dispatcher, $this->factory);
    }
}
