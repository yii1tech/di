<?php

namespace yii1tech\di\console;

use yii1tech\di\base\ResolvesComponentViaDI;

/**
 * {@inheritdoc}
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ConsoleApplication extends \CConsoleApplication
{
    use ResolvesComponentViaDI;
}