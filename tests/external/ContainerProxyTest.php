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

    /**
     * @depends testHas
     */
    public function testCallbackForHas(): void
    {
        $container = new Container();

        $proxy = new ContainerProxy($container);
        $proxy->setCallbackForHas(function (Container $container, $id) {
            return $id === ArrayObject::class;
        });

        $this->assertTrue($proxy->has(ArrayObject::class));
        $this->assertFalse($proxy->has('unexistint-id'));
    }

    /**
     * @depends testGet
     */
    public function testCallbackForGet(): void
    {
        $container = new Container();

        $proxy = new ContainerProxy($container);
        $proxy->setCallbackForGet(function (Container $container, $id) {
            if ($id === ArrayObject::class) {
                return new ArrayObject();
            }

            return null;
        });

        $object = $proxy->get(ArrayObject::class);

        $this->assertTrue($object instanceof ArrayObject);

        $object = $proxy->get('any');
        $this->assertNull($object);
    }

    /**
     * @depends testForwardCall
     */
    public function testClone(): void
    {
        $container = new Container();

        $proxy = new ContainerProxy($container);

        $proxy->instance('before-clone', 'before-clone-data');

        $proxyClone = clone $proxy;

        $proxyClone->instance('after-clone', 'after-clone-data');

        $this->assertTrue($proxyClone->has('before-clone'));
        $this->assertTrue($proxyClone->has('after-clone'));

        $this->assertTrue($proxy->has('before-clone'));
        $this->assertFalse($proxy->has('after-clone'));
    }
}