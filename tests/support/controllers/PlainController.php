<?php

use yii1tech\di\web\Controller;

class PlainController extends Controller
{
    /**
     * @var \ICache
     */
    public $cache;

    /**
     * @var \CFormatter
     */
    public $formatter;

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

    public function actionFormat(CFormatter $formatter, $id): void
    {
        $GLOBALS['controller'] = $this;
        $GLOBALS['method'] = __FUNCTION__;
        $GLOBALS['id'] = $id;

        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'external' => [
                'class' => \yii1tech\di\test\support\controllers\ExternalAction::class,
            ],
        ];
    }
}