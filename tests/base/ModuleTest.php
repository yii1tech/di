<?php

namespace yii1tech\di\test\base;

use CDummyCache;
use CFormatter;
use ICache;
use Yii;
use yii1tech\di\Container;
use yii1tech\di\DI;
use yii1tech\di\test\TestCase;
use yii1tech\di\base\Module;

class ModuleTest extends TestCase
{
    public function testGetComponent(): void
    {
        $container = new Container();
        $cache = new CDummyCache();
        $container->instance(ICache::class, $cache);

        DI::setContainer($container);

        $module = new Module('test', Yii::app(), [
            'components' => [
                'cache' => [
                    'class' => ICache::class,
                ],
                'formatter' => [
                    'class' => CFormatter::class,
                ],
            ],
        ]);

        $component = $module->getComponent('cache', false);
        $this->assertSame(null, $component);

        $component = $module->getComponent('cache', true);
        $this->assertSame($cache, $component);

        $component = $module->getComponent('formatter', false);
        $this->assertSame(null, $component);

        $component = $module->getComponent('formatter', true);
        $this->assertTrue($component instanceof CFormatter);

        $components = $module->getComponents(true);
        $this->assertTrue(isset($components['cache']));
        $this->assertTrue(isset($components['formatter']));
        $this->assertSame($cache, $components['cache']);
    }

    /**
     * @depends testGetComponent
     */
    public function testGetComponentSingleDIAccess(): void
    {
        $container = new Container();

        $counter = 0;
        $container->factory(\stdClass::class, function () use (&$counter) {
            $object = new \stdClass();
            $object->counter = $counter;
            $counter++;

            return $object;
        });

        DI::setContainer($container);

        $module = new Module('test', Yii::app(), [
            'components' => [
                'foo' => [
                    'class' => \stdClass::class,
                ],
            ],
        ]);

        $component = $module->getComponent('foo');
        $this->assertSame($component, $module->getComponent('foo'));
        $this->assertSame(1, $counter);
    }

    public function testSetArbitraryComponent(): void
    {
        $module = new Module('test', Yii::app());

        $component = new \stdClass();
        $module->setComponent('test', $component);

        $this->assertTrue($module->hasComponent('test'));
        $this->assertSame($component, $module->getComponent('test'));
    }

    public function testGetNotExistingComponent(): void
    {
        $module = new Module('test', Yii::app());

        $component = $module->getComponent('not-existing');

        $this->assertNull($component);
    }

    /**
     * @depends testGetComponent
     */
    public function testSetComponentByString(): void
    {
        $container = new Container();
        $cache = new CDummyCache();
        $container->instance(ICache::class, $cache);

        DI::setContainer($container);

        $module = new Module('test', Yii::app(), [
            'components' => [
                'cache' => ICache::class,
            ],
        ]);

        $component = $module->getComponent('cache', true);
        $this->assertSame($cache, $component);
    }
}