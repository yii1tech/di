<?php

namespace yii1tech\di\yii;

/**
 * {@inheritdoc}
 */
class ConsoleApplication extends \CConsoleApplication
{
    use ResolvesComponentViaDI;
}