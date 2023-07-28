<?php

namespace yii1tech\di\test;

use ArrayObject;
use CDbConnection;
use CDummyCache;
use ICache;
use Psr\Container\ContainerInterface;
use yii1tech\di\CircularDependencyException;
use yii1tech\di\Container;
use yii1tech\di\DefinitionNotFoundException;
use yii1tech\di\test\support\DummyWithDependency;

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

    /**
     * @depends testInstance
     */
    public function testAutowire(): void
    {
        $container = new Container();
        $container->instance(CDbConnection::class, new CDbConnection());
        $container->instance(ICache::class, new CDummyCache());

        $container->autowire(DummyWithDependency::class);

        $this->assertTrue($container->has(DummyWithDependency::class));

        $object = $container->get(DummyWithDependency::class);
        $this->assertTrue($object instanceof DummyWithDependency);
        $this->assertSame($container->get(CDbConnection::class), $object->constructorArgs[0]);
        $this->assertSame($container->get(ICache::class), $object->constructorArgs[1]);
    }

    public function testConfig(): void
    {
        $container = new Container();

        $container->config(CDbConnection::class, [
            'username' => 'test-user',
            'autoConnect' => false,
        ]);

        $this->assertTrue($container->has(CDbConnection::class));

        $object = $container->get(CDbConnection::class);
        $this->assertTrue($object instanceof CDbConnection);
        $this->assertSame('test-user', $object->username);
        $this->assertTrue($object->getIsInitialized());
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

    /**
     * @depends testLazy
     */
    public function testCircularDependency(): void
    {
        $container = new Container();

        $container->lazy('one', function (Container $container) {
            return $container->get('two');
        });

        $container->lazy('two', function (Container $container) {
            return $container->get('three');
        });

        $container->lazy('three', function (Container $container) {
            return $container->get('one');
        });

        $this->expectException(CircularDependencyException::class);

        $container->get('one');
    }

    public function testNew(): void
    {
        $container = Container::new();

        $this->assertTrue($container instanceof Container);
    }
}