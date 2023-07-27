<?php

use yii1tech\di\console\ConsoleCommand;

class PlainCommand extends ConsoleCommand
{
    /**
     * @var \ICache
     */
    public $cache;

    /**
     * @var \CFormatter
     */
    public $formatter;

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

    public function actionFormat(CFormatter $formatter, $id): void
    {
        $GLOBALS['command'] = $this;
        $GLOBALS['method'] = __FUNCTION__;
        $GLOBALS['id'] = $id;

        $this->formatter = $formatter;
    }
}