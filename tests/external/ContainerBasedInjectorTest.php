<?php

namespace yii1tech\di\test\external;

use CDbConnection;
use CDummyCache;
use ICache;
use yii1tech\di\external\ContainerBasedInjector;
use yii1tech\di\test\support\DummyWithDependency;
use yii1tech\di\test\support\SelfInjectingContainer;
use yii1tech\di\test\TestCase;

class ContainerBasedInjectorTest extends TestCase
{
    public function testMake(): void
    {
        $container = new SelfInjectingContainer();
        $container->instance(CDbConnection::class, new CDbConnection());
        $container->instance(ICache::class, new CDummyCache());

        $injector = new ContainerBasedInjector();

        /** @var DummyWithDependency $object */
        $object = $injector->make($container, DummyWithDependency::class);

        $this->assertTrue($object instanceof DummyWithDependency);
        $this->assertSame($container->get(CDbConnection::class), $object->constructorArgs[0]);
        $this->assertSame($container->get(ICache::class), $object->constructorArgs[1]);
        $this->assertSame(null, $object->constructorArgs[2]);
        $this->assertSame('tail', $object->constructorArgs[3]);
    }

    /**
     * @depends testMake
     */
    public function testMakeWithArguments(): void
    {
        $container = new SelfInjectingContainer();
        $container->instance(CDbConnection::class, new CDbConnection());

        $injector = new ContainerBasedInjector();

        $cache = new CDummyCache();
        $tail = 'explicit-set-tail';

        /** @var DummyWithDependency $object */
        $object = $injector->make($container, DummyWithDependency::class, ['cache' => $cache, 'tail' => $tail]);

        $this->assertTrue($object instanceof DummyWithDependency);
        $this->assertSame($cache, $object->constructorArgs[1]);
        $this->assertSame($tail, $object->constructorArgs[3]);
    }

    public function testInvoke(): void
    {
        $container = new SelfInjectingContainer();
        $container->instance(CDbConnection::class, new CDbConnection());
        $container->instance(ICache::class, new CDummyCache());

        $injector = new ContainerBasedInjector();

        $result = $injector->invoke($container, [DummyWithDependency::class, 'returnArguments']);

        $this->assertSame($container->get(CDbConnection::class), $result[0]);
        $this->assertSame($container->get(ICache::class), $result[1]);
        $this->assertSame(null, $result[2]);
        $this->assertSame('tail', $result[3]);
    }

    /**
     * @depends testInvoke
     */
    public function testInvokeWithArguments(): void
    {
        $container = new SelfInjectingContainer();
        $container->instance(CDbConnection::class, new CDbConnection());

        $injector = new ContainerBasedInjector();

        $cache = new CDummyCache();
        $tail = 'explicit-set-tail';

        $result = $injector->invoke($container, [DummyWithDependency::class, 'returnArguments'], ['cache' => $cache, 'tail' => $tail]);

        $this->assertSame($cache, $result[1]);
        $this->assertSame($tail, $result[3]);
    }
}