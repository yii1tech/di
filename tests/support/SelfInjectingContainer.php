<?php

namespace yii1tech\di\test\support;

use yii1tech\di\Container;
use yii1tech\di\external\ContainerProxy;
use yii1tech\di\Injector;

/**
 * @method instance(string $id, mixed $object)
 */
class SelfInjectingContainer extends ContainerProxy
{
    public function __construct()
    {
        parent::__construct(new Container());
    }

    public function call(callable $callable, array $arguments = [])
    {
        return (new Injector())->invoke($this, $callable, $arguments);
    }

    public function make(string $class, array $arguments = [])
    {
        return (new Injector())->make($this, $class, $arguments);
    }
}