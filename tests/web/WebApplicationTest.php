<?php

namespace web;

use CDummyCache;
use ICache;
use Yii;
use yii1tech\di\Container;
use yii1tech\di\DI;
use yii1tech\di\test\support\controllers\NamespaceController;
use yii1tech\di\test\TestCase;
use yii1tech\di\web\WebApplication;

class WebApplicationTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication(
            [
                'controllerPath' => __DIR__ . '/../support/controllers'
            ],
            WebApplication::class
        );

        $container = new Container();
        $cache = new CDummyCache();
        $container->instance(ICache::class, $cache);

        DI::setContainer($container);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($GLOBALS['controller']);
        unset($GLOBALS['method']);

        parent::tearDown();
    }

    public function testCreateController(): void
    {
        Yii::app()->runController('plain/index');

        $this->assertTrue(isset($GLOBALS['controller']));
        $this->assertTrue(isset($GLOBALS['method']));

        /** @var \PlainController $controller */
        $controller = $GLOBALS['controller'];
        $this->assertTrue($controller instanceof \PlainController);
        $this->assertTrue($controller->cache instanceof ICache);
        $this->assertSame('actionIndex', $GLOBALS['method']);
        $this->assertSame('plain', $controller->id);
        $this->assertSame(null, $controller->module);
    }

    public function testCreateControllerByMap(): void
    {
        Yii::app()->controllerPath = __DIR__;
        Yii::app()->controllerMap['test'] = [
            'class' => NamespaceController::class,
            'mode' => 'map',
        ];

        Yii::app()->runController('test/index');

        $this->assertTrue(isset($GLOBALS['controller']));
        $this->assertTrue(isset($GLOBALS['method']));

        /** @var NamespaceController $controller */
        $controller = $GLOBALS['controller'];
        $this->assertTrue($controller instanceof NamespaceController);
        $this->assertTrue($controller->cache instanceof ICache);
        $this->assertSame('actionIndex', $GLOBALS['method']);
        $this->assertSame('test', $controller->id);
        $this->assertSame('map', $controller->mode);
        $this->assertSame(null, $controller->module);
    }

    public function testCreateControllerByNamespace(): void
    {
        Yii::app()->controllerNamespace = 'yii1tech\di\test\support\controllers';

        Yii::app()->runController('namespace/index');

        $this->assertTrue(isset($GLOBALS['controller']));
        $this->assertTrue(isset($GLOBALS['method']));

        /** @var NamespaceController $controller */
        $controller = $GLOBALS['controller'];
        $this->assertTrue($controller instanceof NamespaceController);
        $this->assertTrue($controller->cache instanceof ICache);
        $this->assertSame('actionIndex', $GLOBALS['method']);
        $this->assertSame('namespace', $controller->id);
        $this->assertSame(null, $controller->module);
    }
}