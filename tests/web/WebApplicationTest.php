<?php

namespace yii1tech\di\test\web;

use CDummyCache;
use CFormatter;
use CHttpException;
use ICache;
use Yii;
use yii1tech\di\Container;
use yii1tech\di\DI;
use yii1tech\di\test\support\controllers\ExternalAction;
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

        $container->instance(ICache::class, new CDummyCache());
        $container->instance(CFormatter::class, new CFormatter());

        DI::setContainer($container);
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

    /**
     * @depends testCreateController
     */
    public function testRunActionWithParams(): void
    {
        $_GET['id'] = 123;

        Yii::app()->runController('plain/format');

        $this->assertTrue(isset($GLOBALS['controller']));

        /** @var \PlainController $controller */
        $controller = $GLOBALS['controller'];
        $this->assertTrue($controller instanceof \PlainController);

        $this->assertTrue($controller->formatter instanceof CFormatter);

        $this->assertTrue(isset($GLOBALS['id']));
        $this->assertEquals($_GET['id'], $GLOBALS['id']);
    }

    /**
     * @depends testRunActionWithParams
     */
    public function testRunActionWithMissingParams(): void
    {
        try {
            Yii::app()->runController('plain/format');
        } catch (CHttpException $exception) {
            // blank
        }

        $this->assertTrue(isset($exception));
        $this->assertEquals(400, $exception->statusCode);
    }

    /**
     * @depends testCreateController
     */
    public function testRunExternalAction(): void
    {
        $_GET['id'] = 123;

        Yii::app()->runController('plain/external');

        $this->assertTrue(isset($GLOBALS['controller']));
        $this->assertTrue(isset($GLOBALS['action']));

        /** @var ExternalAction $action */
        $action = $GLOBALS['action'];
        $this->assertTrue($action instanceof ExternalAction);
        $this->assertTrue($action->cache instanceof ICache);
        $this->assertTrue($action->formatter instanceof CFormatter);

        $this->assertTrue(isset($GLOBALS['id']));
        $this->assertEquals($_GET['id'], $GLOBALS['id']);
    }

    /**
     * @depends testRunExternalAction
     */
    public function testRunExternalActionWithMissingParams(): void
    {
        try {
            Yii::app()->runController('plain/external');
        } catch (CHttpException $exception) {
            // blank
        }

        $this->assertTrue(isset($exception));
        $this->assertEquals(400, $exception->statusCode);
    }
}