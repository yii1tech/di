<?php

namespace yii1tech\di;

use Psr\Container\ContainerInterface;

/**
 * InjectorContract defines the interface for dependency injector.
 *
 * Injector should be able to analyze callable dependencies based on type hinting and inject them from any PSR-11 compatible container.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface InjectorContract
{
    /**
     * Invoke a callback with resolving dependencies based on parameter types.
     *
     * @param \Psr\Container\ContainerInterface $container DI container instance.
     * @param callable $callable callable to be invoked.
     * @param array $arguments list of function arguments.
     * @return mixed invocation result.
     */
    public function invoke(ContainerInterface $container, callable $callable, array $arguments = []);

    /**
     * Creates an object of a given class with resolving constructor dependencies based on parameter types.
     *
     * @param \Psr\Container\ContainerInterface $container DI container instance.
     * @param string $class class name.
     * @param array $arguments list of constructor arguments.
     * @return mixed created class instance.
     */
    public function make(ContainerInterface $container, string $class, array $arguments = []);
}