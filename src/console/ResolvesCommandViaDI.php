<?php

namespace yii1tech\di\console;

use CHelpCommand;
use Yii;
use yii1tech\di\DI;

/**
 * ResolvesCommandViaDI allows dependency injection at the console command constructor level.
 *
 * It analyzes command's constructor signature and passes entities from the PSR compatible container based on type-hinting.
 *
 * @mixin \CConsoleCommandRunner
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait ResolvesCommandViaDI
{
    /**
     * {@inheritdoc}
     */
    public function createCommand($name)
    {
        $name = strtolower($name);

        $command = null;
        if (isset($this->commands[$name])) {
            $command = $this->commands[$name];
        } else {
            $commands = array_change_key_case($this->commands);
            if (isset($commands[$name])) {
                $command = $commands[$name];
            }
        }

        if ($command === null) {
            if ($name === 'help') {
                if (DI::has(CHelpCommand::class)) {
                    return DI::get(CHelpCommand::class);
                }

                return new CHelpCommand('help', $this);
            }

            return null;
        }

        if (is_string($command)) { // class file path or alias
            if (strpos($command, '/') !== false || strpos($command, '\\') !== false) {
                $className = substr(basename($command), 0, -4);

                if (isset($this->commandNamespace)) {
                    $className = $this->commandNamespace . '\\' . $className;
                }

                if (!class_exists($className, false)) {
                    require_once($command);
                }
            } else {// an alias
                $className = Yii::import($command);
            }

            return DI::make($className, [
                'name' => $name,
                'runner' => $this,
            ]);
        }

        return DI::create($command, [
            'name' => $name,
            'runner' => $this,
        ]);
    }
}