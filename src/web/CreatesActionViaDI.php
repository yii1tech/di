<?php

namespace yii1tech\di\web;

use CException;
use Yii;
use yii1tech\di\DI;

/**
 * @mixin \CController
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait CreatesActionViaDI
{
    /**
     * {@inheritdoc}
     */
    public function createAction($actionID)
    {
        if ($actionID === '') {
            $actionID = $this->defaultAction;
        }

        if (method_exists($this, 'action' . $actionID) && strcasecmp($actionID, 's')) {// we have actions method
            return new InlineAction($this, $actionID);
        }

        return parent::createAction($actionID);
    }

    /**
     * {@inheritdoc}
     */
    protected function createActionFromMap($actionMap, $actionID, $requestActionID, $config = [])
    {
        if(($pos = strpos($actionID, '.')) === false && isset($actionMap[$actionID])) {
            $config = array_merge(
                is_array($actionMap[$actionID]) ? $actionMap[$actionID] : ['class' => $actionMap[$actionID]],
                $config
            );

            if (isset($config['class'])) {
                $class = $config['class'];
                unset($config['class']);
            } else {
                throw new CException(Yii::t('yii', 'Object configuration must be an array containing a "class" element.'));
            }

            $action = DI::make($class, [
                'controller' => $this,
                'id' => $requestActionID,
            ]);

            foreach ($config as $name => $value) {
                $action->$name = $value;
            }

            return $action;
        }

        return parent::createActionFromMap($actionMap, $actionID, $requestActionID, $config);
    }
}