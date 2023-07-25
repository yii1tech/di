<?php

namespace yii1tech\di;

use Psr\Container\ContainerInterface;

/**
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Injector implements InjectorContract
{
    /**
     * {@inheritdoc}
     */
    public function invoke(ContainerInterface $container, callable $callable, array $arguments = [])
    {
        // TODO: Implement invoke() method.
    }

    /**
     * {@inheritdoc}
     */
    public function make(ContainerInterface $container, string $class, array $arguments = [])
    {
        // TODO: Implement make() method.
    }
}