<?php

namespace yii1tech\di\test;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use yii1tech\di\Container;
use yii1tech\di\DI;
use yii1tech\di\Injector;
use yii1tech\di\InjectorContract;
use yii1tech\di\test\support\Dummy;

class DITest extends TestCase
{
    public function testSetupContainer(): void
    {
        $container = new Container();

        DI::setContainer($container);

        $this->assertSame($container, DI::getContainer());
        $this->assertSame(DI::getContainer(), DI::container());
    }

    public function testSetupInjector(): void
    {
        $injector = new Injector();

        DI::setInjector($injector);

        $this->assertSame($injector, DI::getInjector());
        $this->assertSame(DI::getInjector(), DI::injector());
    }

    /**
     * @depends testSetupContainer
     * @depends testSetupInjector
     */
    public function testGetDefaults(): void
    {
        $container = DI::container();

        $this->assertTrue($container instanceof ContainerInterface);

        $injector = DI::getInjector();

        $this->assertTrue($injector instanceof InjectorContract);
    }

    /**
     * @depends testGetDefaults
     */
    public function testInvoke(): void
    {
        $result = DI::invoke([Dummy::class, 'returnArguments']);
        $this->assertSame('default', $result[0]);

        $result = DI::invoke([Dummy::class, 'returnArguments'], ['foo' => 'bar']);
        $this->assertSame('bar', $result[0]);
    }

    /**
     * @depends testGetDefaults
     */
    public function testMake(): void
    {
        $object = DI::make(Dummy::class);

        $this->assertTrue($object instanceof Dummy);

        $object = DI::make(Dummy::class, ['foo' => 'bar']);

        $this->assertTrue($object instanceof Dummy);
        $this->assertSame('bar', $object->constructorArgs[0]);
    }

    /**
     * @depends testMake
     */
    public function testCreate(): void
    {
        $object = DI::create(Dummy::class);

        $this->assertTrue($object instanceof Dummy);

        $object = DI::create([
            'class' => Dummy::class,
            'name' => 'test',
        ]);

        $this->assertTrue($object instanceof Dummy);
        $this->assertSame('test', $object->name);

        $object = DI::make(Dummy::class, ['foo' => 'bar']);

        $this->assertTrue($object instanceof Dummy);
        $this->assertSame('bar', $object->constructorArgs[0]);
    }

    /**
     * @depends testCreate
     */
    public function testCreateWithYiiImport(): void
    {
        $object = DI::create([
            'class' => 'zii.behaviors.CTimestampBehavior',
        ]);

        $this->assertFalse(empty($object));
    }

    /**
     * @depends testSetupContainer
     */
    public function testHas(): void
    {
        $container = new Container();
        $container->instance(Dummy::class, new Dummy());

        DI::setContainer($container);

        $this->assertTrue(DI::has(Dummy::class));
        $this->assertFalse(DI::has('Unexisting\\Class'));
    }

    /**
     * @depends testSetupContainer
     */
    public function testGet(): void
    {
        $container = new Container();
        $object = new Dummy();
        $container->instance(Dummy::class, $object);

        DI::setContainer($container);

        $this->assertSame($object, DI::get(Dummy::class));

        $this->expectException(NotFoundExceptionInterface::class);

        DI::get('Unexisting\\Class');
    }
}