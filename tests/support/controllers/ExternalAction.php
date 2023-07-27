<?php

namespace yii1tech\di\test\support\controllers;

use CFormatter;
use ICache;
use yii1tech\di\web\Action;

class ExternalAction extends Action
{
    /**
     * @var \ICache
     */
    public $cache;
    /**
     * @var \CFormatter
     */
    public $formatter;

    public function __construct(ICache $cache, $controller, $id)
    {
        parent::__construct($controller, $id);

        $this->cache = $cache;
    }

    public function run(CFormatter $formatter, $id): void
    {
        $GLOBALS['controller'] = $this->getController();
        $GLOBALS['action'] = $this;
        $GLOBALS['method'] = __FUNCTION__;
        $GLOBALS['id'] = $id;

        $this->formatter = $formatter;
    }
}