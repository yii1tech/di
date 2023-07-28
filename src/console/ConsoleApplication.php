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
    use ResolvesCommandRunnerViaDI;
    use MatchesWebApplicationNotation;

    /**
     * @var string|null namespace that should be used when loading commands.
     * Default is to use global namespace.
     */
    public $commandNamespace;
}