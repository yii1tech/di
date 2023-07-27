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
}