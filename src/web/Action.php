<?php

namespace yii1tech\di\web;

/**
 * {@inheritdoc}
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Action extends \CAction
{
    use RunsActionWithParamsViaDI;
}