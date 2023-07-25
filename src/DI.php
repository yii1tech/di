<?php

namespace yii1tech\di;

use Psr\Container\ContainerInterface;

/**
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

    public static function setContainer($container): self
    {
        self::$container = $container;

        return new static();
    }

    public static function getContainer(): ContainerInterface
    {
        if (self::$container === null) {
            throw new \LogicException('"' . get_called_class() . '::$container" has not been configured.');
        }

        if (!is_object(self::$container) || !self::$container instanceof ContainerInterface) {
            self::$container = call_user_func(self::$container);
        }

        return self::$container;
    }

    public static function setInjector($injector): self
    {
        self::$injector = $injector;

        return new static();
    }

    public static function getInjector(): InjectorContract
    {
        if (self::$injector === null) {
            throw new \LogicException('"' . get_called_class() . '::injector" has not been configured.');
        }

        if (!is_object(self::$injector) || !self::$injector instanceof InjectorContract) {
            self::$injector = call_user_func(self::$injector);
        }

        return self::$injector;
    }

    public static function container(): ContainerInterface
    {
        return static::getContainer();
    }

    public static function injector(): InjectorContract
    {
        return static::getInjector();
    }

    public static function make(string $class, array $arguments = [])
    {
        return static::injector()->make(static::container(), $class, $arguments);
    }

    public static function invoke(callable $callable, array $arguments = [])
    {
        return static::injector()->invoke(static::container(), $callable, $arguments);
    }
}