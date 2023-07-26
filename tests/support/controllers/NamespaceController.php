<?php

namespace yii1tech\di\test\support\controllers;

class NamespaceController extends \CController
{
    /**
     * @var \ICache
     */
    public $cache;

    /**
     * @var string
     */
    public $mode = 'default';

    public function __construct(\ICache $cache, $id, $module = null)
    {
        parent::__construct($id, $module);

        $this->cache = $cache;
    }

    public function actionIndex(): void
    {
        $GLOBALS['controller'] = $this;
        $GLOBALS['method'] = __FUNCTION__;
    }
}