<?php

namespace yii1tech\di\test;

use CConsoleApplication;
use CMap;
use Yii;
use yii1tech\di\DI;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        DI::setContainer(null);
        DI::setInjector(null);

        $this->mockApplication();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->destroyApplication();
    }

    /**
     * Populates Yii::app() with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = CConsoleApplication::class)
    {
        Yii::setApplication(null);

        new $appClass(CMap::mergeArray([
            'id' => 'testapp',
            'basePath' => __DIR__,
        ], $config));
    }

    /**
     * Destroys Yii application by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::setApplication(null);
    }
}