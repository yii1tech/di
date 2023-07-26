<?php

namespace yii1tech\di\test;

use yii1tech\di\Container;
use yii1tech\di\DI;
use yii1tech\di\Injector;

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
}