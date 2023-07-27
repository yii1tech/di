<?php

namespace yii1tech\di\console;

use Yii;

/**
 * {@inheritdoc}
 *
 * @property string|null $commandNamespace namespace that should be used when loading commands.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ConsoleCommandRunner extends \CConsoleCommandRunner
{
    use ResolvesCommandViaDI;

    /**
     * @var string|null namespace that should be used when loading commands.
     * Default is to use global namespace.
     */
    private $_commandNamespace;

    /**
     * @return string|null
     */
    public function getCommandNamespace(): ?string
    {
        if ($this->_commandNamespace === null) {
            $app = Yii::app();

            if (isset($app->commandNamespace)) {
                $this->commandNamespace = $app->commandNamespace;
            }
        }

        return $this->_commandNamespace;
    }

    /**
     * @param string|null $commandNamespace
     * @return static self reference.
     */
    public function setCommandNamespace(?string $commandNamespace): self
    {
        $this->_commandNamespace = $commandNamespace;

        return $this;
    }
}