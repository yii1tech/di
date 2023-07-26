<?php

class PlainController extends \CController
{
    /**
     * @var \ICache
     */
    public $cache;

    public function __construct(ICache $cache, $id, $module = null)
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