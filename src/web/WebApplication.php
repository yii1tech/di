<?php

namespace yii1tech\di\web;

use yii1tech\di\base\ResolvesComponentViaDI;

/**
 * {@inheritdoc}
 */
class WebApplication extends \CWebApplication
{
    use ResolvesComponentViaDI;
    use ResolvesControllerViaDI;
}