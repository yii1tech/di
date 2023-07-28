<?php

namespace yii1tech\di\test\external;

use ArrayObject;
use yii1tech\di\Container;
use yii1tech\di\external\ContainerProxy;
use yii1tech\di\test\TestCase;

class ContainerProxyTest extends TestCase
{
    public function testHas(): void
    {
        $container = new Container();

        $container->instance(ArrayObject::class, new ArrayObject());

        $proxy = new ContainerProxy($container);

        $this->assertTrue($proxy->has(ArrayObject::class));
        $this->assertFalse($proxy->has('unexistint-id'));
    }

    public function testGet(): void
    {
        $container = new Container();

        $object = new ArrayObject();
        $container->instance(ArrayObject::class, $object);

        $proxy = new ContainerProxy($container);

        $this->assertSame($object, $proxy->get(ArrayObject::class));
    }

    /**
     * @depends testHas
     */
    public function testForwardCall(): void
    {
        $container = new Container();

        $proxy = new ContainerProxy($container);

        $proxy->instance(ArrayObject::class, new ArrayObject());

        $this->assertTrue($proxy->has(ArrayObject::class));
    }
}