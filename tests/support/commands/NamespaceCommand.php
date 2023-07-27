<?php

namespace yii1tech\di\test\support\commands;

use ICache;
use yii1tech\di\console\ConsoleCommand;

class NamespaceCommand extends ConsoleCommand
{
    /**
     * @var \ICache
     */
    public $cache;

    public function __construct(ICache $cache, $name, $runner)
    {
        parent::__construct($name, $runner);

        $this->cache = $cache;
    }

    public function actionIndex(): void
    {
        $GLOBALS['command'] = $this;
        $GLOBALS['method'] = __FUNCTION__;
    }
}