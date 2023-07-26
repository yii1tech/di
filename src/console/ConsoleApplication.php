<?php

namespace yii1tech\di\console;

use yii1tech\di\base\ResolvesComponentViaDI;

/**
 * {@inheritdoc}
 */
class ConsoleApplication extends \CConsoleApplication
{
    use ResolvesComponentViaDI;
}