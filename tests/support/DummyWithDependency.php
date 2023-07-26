<?php

namespace yii1tech\di\test\support;

class DummyWithDependency
{
    public $constructorArgs = [];

    public function __construct(\CDbConnection $db, \ICache $cache, ?\IUserIdentity $user = null, $tail = 'tail')
    {
        $this->constructorArgs = func_get_args();
    }

    public static function returnArguments(\CDbConnection $db, \ICache $cache, ?\IUserIdentity $user = null, $tail = 'tail'): array
    {
        return func_get_args();
    }
}