<?php

namespace yii1tech\di\web;

use yii1tech\di\DI;

/**
 * {@inheritdoc}
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Action extends \CAction
{
    /**
     * {@inheritdoc}
     */
    public function runWithParams($params)
    {
        DI::invoke([$this, 'run'], $params);

        return true;
    }
}