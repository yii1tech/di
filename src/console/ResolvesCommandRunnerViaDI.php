<?php

namespace yii1tech\di\console;

use yii1tech\di\DI;

/**
 * ResolvesCommandRunnerViaDI bootstraps DI aware command runner to the console application.
 *
 * @mixin \CConsoleApplication
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait ResolvesCommandRunnerViaDI
{
    /**
     * {@inheritdoc}
     */
    protected function createCommandRunner()
    {
        if (DI::has(\CConsoleCommandRunner::class)) {
            return DI::get(\CConsoleCommandRunner::class);
        }

        return new ConsoleCommandRunner();
    }
}