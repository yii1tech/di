<?php

namespace yii1tech\di\web;

use yii1tech\di\base\ResolvesComponentViaDI;

/**
 * {@inheritdoc}
 */
class WebModule extends \CWebModule
{
    use ResolvesComponentViaDI;
}