<?php

namespace yii1tech\di;

use Psr\Container\ContainerInterface;

/**
 * DI is a facade for global access to PSR compatible container and injector.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class DI
{
    /**
     * @var \Psr\Container\ContainerInterface|callable DI container instance.
     */
    private static $container;
    /**
     * @var \yii1tech\di\InjectorContract|callable injector instance.
     */
    private static $injector;

    /**
     * Sets PSR compatible container to be used.
     *
     * @param \Psr\Container\ContainerInterface|callable|null $container PSR compatible container or a callback, which instantiates it.
     * @return static self reference, can be used to chain methods calls.
     */
    public static function setContainer($container): self
    {
        self::$container = $container;

        return new static();
    }

    /**
     * Returns currently used PSR compatible container.
     *
     * @return \Psr\Container\ContainerInterface PSR compatible container instance.
     */
    public static function getContainer(): ContainerInterface
    {
        if (self::$container === null) {
            self::$container = new Container();
        }

        if (!is_object(self::$container) || !self::$container instanceof ContainerInterface) {
            self::$container = call_user_func(self::$container);
        }

        return self::$container;
    }

    /**
     * Sets injector instance to be used.
     *
     * @param \yii1tech\di\InjectorContract|callable|null $injector injector instance or a callback, which instantiates it.
     * @return static self reference, can be used to chain methods calls.
     */
    public static function setInjector($injector): self
    {
        self::$injector = $injector;

        return new static();
    }

    /**
     * Returns currently used injector.
     *
     * @return \yii1tech\di\InjectorContract injector instance.
     */
    public static function getInjector(): InjectorContract
    {
        if (self::$injector === null) {
            self::$injector = new Injector();
        }

        if (!is_object(self::$injector) || !self::$injector instanceof InjectorContract) {
            self::$injector = call_user_func(self::$injector);
        }

        return self::$injector;
    }

    /**
     * Alias of {@see getContainer()}.
     *
     * @return \Psr\Container\ContainerInterface PSR compatible container instance.
     */
    public static function container(): ContainerInterface
    {
        return static::getContainer();
    }

    /**
     * Alias of {@see getInjector()}.
     *
     * @return \yii1tech\di\InjectorContract injector instance.
     */
    public static function injector(): InjectorContract
    {
        return static::getInjector();
    }

    /**
     * Creates an object of a given class with resolving constructor dependencies based on parameter types.
     *
     * @param string $class class name.
     * @param array $arguments list of constructor arguments.
     * @return mixed created class instance.
     */
    public static function make(string $class, array $arguments = [])
    {
        return static::injector()->make(static::container(), $class, $arguments);
    }

    /**
     * Invoke a callback with resolving dependencies based on parameter types.
     *
     * @param callable $callable callable to be invoked.
     * @param array $arguments list of function arguments.
     * @return mixed invocation result.
     */
    public static function invoke(callable $callable, array $arguments = [])
    {
        return static::injector()->invoke(static::container(), $callable, $arguments);
    }
}