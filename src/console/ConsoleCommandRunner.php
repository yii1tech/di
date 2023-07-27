<?php

namespace yii1tech\di\console;

/**
 * {@inheritdoc}
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ConsoleCommandRunner extends \CConsoleCommandRunner
{
    use ResolvesCommandViaDI;
}