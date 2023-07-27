<?php

namespace yii1tech\di\web;

use yii1tech\di\DI;

/**
 * {@inheritdoc}
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class InlineAction extends \CInlineAction
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->runWithParams([]);
    }

    /**
     * {@inheritdoc}
     */
    public function runWithParams($params)
    {
        $method = 'action' . $this->getId();

        DI::invoke([$this->getController(), $method], $params);

        return true;
    }
}