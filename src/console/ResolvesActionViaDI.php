<?php

namespace yii1tech\di\console;

use ReflectionClass;
use ReflectionMethod;
use yii1tech\di\DI;

/**
 * ResolvesActionViaDI allows dependency injection at the controller action level.
 *
 * It analyzes action's method signature and passes entities from the PSR compatible container based on type-hinting.
 *
 * @mixin \CConsoleCommand
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait ResolvesActionViaDI
{
    /**
     * {@inheritdoc}
     */
    public function run($args)
    {
        [$action, $options, $args] = $this->resolveRequest($args);

        $methodName = 'action' . $action;
        if (!preg_match('/^\w+$/', $action) || !method_exists($this, $methodName)) {
            $this->usageError("Unknown action: " . $action);

            return 1;
        }

        $method = new ReflectionMethod($this, $methodName);
        if (!$method->isPublic()) {
            $this->usageError("Unknown action: " . $action);

            return 1;
        }

        $params = [];
        $arguments = [];
        // named and unnamed options
        foreach ($method->getParameters() as $i => $param) {
            $name = $param->getName();

            if ($param->hasType() && !$param->getType()->isBuiltin()) {
                if (DI::has($param->getType()->getName())) {
                    $arguments[$name] = DI::get($param->getType()->getName());

                    continue;
                }
            }

            if (isset($options[$name])) {
                if (version_compare(PHP_VERSION,'8.0','>=')) {
                    $isArray = $param->hasType() && $param->getType()->getName() === 'array';
                } else {
                    $isArray = $param->isArray();
                }

                if ($isArray) {
                    $params[$name] = is_array($options[$name]) ? $options[$name] : [$options[$name]];
                } elseif (!is_array($options[$name])) {
                    $params[$name] = $options[$name];
                } else {
                    $this->usageError("Option --$name requires a scalar. Array is given.");

                    return 1;
                }

                $arguments[$name] = $params[$name];
            } elseif ($name === 'args') {
                $params[$name] = $args;
                $arguments[$name] = $args;
            } elseif ($param->isDefaultValueAvailable()) {
                $params[$name] = $param->getDefaultValue();
                $arguments[$name] = $params[$name];
            } else {
                $this->usageError("Missing required option --$name.");

                return 1;
            }

            unset($options[$name]);
        }

        // try global options
        if (!empty($options)) {
            $class = new ReflectionClass(get_class($this));
            foreach ($options as $name => $value) {
                if ($class->hasProperty($name)) {
                    $property = $class->getProperty($name);
                    if ($property->isPublic() && !$property->isStatic()) {
                        $this->$name = $value;
                        unset($options[$name]);
                    }
                }
            }
        }

        if (!empty($options)) {
            $this->usageError("Unknown options: " . implode(', ', array_keys($options)));

            return 1;
        }

        $params = array_values($params);

        $exitCode = 0;
        if ($this->beforeAction($action, $params)) {
            $exitCode = $method->invokeArgs($this, $arguments);
            $exitCode = $this->afterAction($action, $params, is_int($exitCode) ? $exitCode : 0);
        }

        return $exitCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionHelp()
    {
        $options = [];
        $class = new ReflectionClass(get_class($this));

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();
            if (!strncasecmp($name, 'action', 6) && strlen($name) > 6) {
                $options[] = $this->buildActionHelp($method);
            }
        }

        return $options;
    }

    /**
     * Builds help text for the particular console command action.
     *
     * @since 1.0.2
     *
     * @param \ReflectionMethod $method action method reflection.
     * @return string help text.
     */
    protected function buildActionHelp(ReflectionMethod $method): string
    {
        $name = substr($method->getName(), 6);
        $name[0] = strtolower($name[0]);
        $help = $name;

        foreach ($method->getParameters() as $param) {
            if ($param->hasType() && !$param->getType()->isBuiltin()) {
                continue;
            }

            $optional = $param->isDefaultValueAvailable();
            $defaultValue = $optional ? $param->getDefaultValue() : null;
            if (is_array($defaultValue)) {
                $defaultValue = str_replace(["\r\n", "\n", "\r"], '', print_r($defaultValue, true));
            }
            $name = $param->getName();

            if ($name === 'args') {
                continue;
            }

            if ($optional) {
                $help .= " [--$name=$defaultValue]";
            } else {
                $help .= " --$name=value";
            }
        }

        return $help;
    }
}