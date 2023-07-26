<?php

namespace yii1tech\di\test\support;

class Dummy
{
    public $constructorArgs = [];

    public function __construct($foo = 'default')
    {
        $this->constructorArgs = func_get_args();
    }

    public static function returnArguments($foo = 'default'): array
    {
        return func_get_args();
    }
}