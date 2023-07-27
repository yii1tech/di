<?php

namespace yii1tech\di;

use CException;
use Psr\Container\ContainerInterface;
use Yii;

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
     * Finds an entry of the decorated container by its identifier and returns it.
     *
     * @param string $id identifier of the entry to look for.
     * @return mixed entry.
     * @throws \Psr\Container\ContainerExceptionInterface no entry was found for **this** identifier.
     * @throws \Psr\Container\NotFoundExceptionInterface error while retrieving the entry.
     */
    public static function get(string $id)
    {
        return static::container()->get($id);
    }

    /**
     * Returns `true` if the decorated container can return an entry for the given identifier.
     * Returns `false` otherwise.
     *
     * @param string $id identifier of the entry to look for.
     * @return bool whether entry with given id exists or not.
     */
    public static function has(string $id): bool
    {
        return static::container()->has($id);
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
     * Creates an object from the Yii-style configuration with resolving constructor dependencies based on parameter types.
     * @see \YiiBase::createComponent()
     *
     * @param array|string $config the configuration. It can be either a string or an array.
     * @param array $arguments list of constructor arguments.
     * @return mixed the created object.
     * @throws \CException on invalid configuration.
     */
    public static function create($config, array $arguments = [])
    {
        if (is_string($config)) {
            $class = $config;
            $config = [];
        } elseif (isset($config['class'])) {
            $class = $config['class'];
            unset($config['class']);
        } else {
            throw new CException(Yii::t('yii', 'Object configuration must be an array containing a "class" element.'));
        }

        $object = static::make($class, $arguments);

        foreach ($config as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }
}