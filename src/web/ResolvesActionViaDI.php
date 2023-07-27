<?php

namespace yii1tech\di\web;

use yii1tech\di\DI;

/**
 * ResolvesActionViaDI allows dependency injection at the controller action level.
 *
 * It analyzes action's method signature and passes entities from the PSR compatible container based on type-hinting.
 *
 * @mixin \CController
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait ResolvesActionViaDI
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

            return DI::create($config, [
                'controller' => $this,
                'id' => $requestActionID,
            ]);
        }

        return parent::createActionFromMap($actionMap, $actionID, $requestActionID, $config);
    }
}