<?php

namespace yii1tech\di\test;

use ArrayObject;
use Psr\Container\ContainerInterface;
use yii1tech\di\Container;
use yii1tech\di\DefinitionNotFoundException;

class ContainerTest extends TestCase
{
    public function testInstance(): void
    {
        $container = new Container();

        $object = new ArrayObject();

        $container->instance(ArrayObject::class, $object);

        $this->assertTrue($container->has(ArrayObject::class));

        $this->assertSame($object, $container->get(ArrayObject::class));
    }

    public function testLazy(): void
    {
        $container = new Container();

        $container->lazy(ArrayObject::class, function () {
            return new ArrayObject(['foo' => 'bar']);
        });

        $this->assertTrue($container->has(ArrayObject::class));

        $object = $container->get(ArrayObject::class);

        $this->assertTrue($object instanceof ArrayObject);
        $this->assertSame('bar', $object['foo']);

        $this->assertSame($object, $container->get(ArrayObject::class));
    }

    public function testFactory(): void
    {
        $container = new Container();

        $container->factory(ArrayObject::class, function () {
            return new ArrayObject(['foo' => 'bar']);
        });

        $this->assertTrue($container->has(ArrayObject::class));

        $object = $container->get(ArrayObject::class);

        $this->assertTrue($object instanceof ArrayObject);
        $this->assertSame('bar', $object['foo']);

        $this->assertNotSame($object, $container->get(ArrayObject::class));
    }

    public function testGetSelf(): void
    {
        $container = new Container();

        $this->assertTrue($container->has(Container::class));
        $this->assertSame($container, $container->get(Container::class));

        $this->assertTrue($container->has(ContainerInterface::class));
        $this->assertSame($container, $container->get(ContainerInterface::class));
    }

    public function testMissingDefinition(): void
    {
        $container = new Container();

        $this->assertFalse($container->has(ArrayObject::class));

        $this->expectException(DefinitionNotFoundException::class);

        $object = $container->get(ArrayObject::class);
    }
}