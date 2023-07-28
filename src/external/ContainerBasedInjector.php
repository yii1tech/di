<?php

namespace yii1tech\di\external;

use Psr\Container\ContainerInterface;
use yii1tech\di\InjectorContract;

/**
 * ContainerBasedInjector forwards dependency injection resolution to the container.
 *
 * It expects passing container to implement methods `make()` and `call()`, since it is widely used notation.
 * This injection can be used with some external (3rd party) containers.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ContainerBasedInjector implements InjectorContract
{
    /**
     * {@inheritdoc}
     */
    public function invoke(ContainerInterface $container, callable $callable, array $arguments = [])
    {
        return $container->call($callable, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function make(ContainerInterface $container, string $class, array $arguments = [])
    {
        return $container->make($class, $arguments);
    }
}