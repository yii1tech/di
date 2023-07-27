<?php

namespace yii1tech\di\web;

use yii1tech\di\base\ResolvesComponentViaDI;

/**
 * {@inheritdoc}
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class WebApplication extends \CWebApplication
{
    use ResolvesComponentViaDI;
    use ResolvesControllerViaDI;
}