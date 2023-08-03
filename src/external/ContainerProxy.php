<?php

namespace yii1tech\di\external;

use Psr\Container\ContainerInterface;

/**
 * ContainerProxy wraps another container and forwards all method calls to it.
 *
 * This class can be used to resolve inconsistencies in particular container implementation.
 * Usually to fix behavior of `has()` method.
 *
 * For example:
 *
 * ```php
 * use DI\Container;
 * use yii1tech\di\external\ContainerProxy;
 *
 * $container = ContainerProxy::new(new Container())
 *     ->setCallbackForHas(function (Container $container, string $id) {
 *         return in_array($id, $container->getKnownEntryNames(), true);
 *     });
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ContainerProxy implements ContainerInterface
{
    /**
     * @var \Psr\Container\ContainerInterface wrapped container instance.
     */
    protected $container;
    /**
     * @var callable|null PHP callback, which should be used to implement `get()` method.
     */
    protected $callbackForGet;
    /**
     * @var callable|null PHP callback, which should be used to implement `has()` method.
     */
    protected $callbackForHas;

    /**
     * Constructor.
     *
     * @param \Psr\Container\ContainerInterface $container container instance to be wrapped.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id)
    {
        if ($this->callbackForGet === null) {
            return $this->container->get($id);
        }

        return call_user_func($this->callbackForGet, $this->container, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        if ($this->callbackForHas == null) {
            return $this->container->has($id);
        }

        return call_user_func($this->callbackForHas, $this->container, $id);
    }

    /**
     * Specifies a PHP callback, which should be invoked to implement method `get()`.
     * The callback signature:
     *
     * ```
     * function (\Psr\Container\ContainerInterface $container, string $id): mixed
     * ```
     *
     * @param callable|null $callback PHP callback.
     * @return static self reference.
     */
    public function setCallbackForGet(?callable $callback): self
    {
        $this->callbackForGet = $callback;

        return $this;
    }

    /**
     * Specifies a PHP callback, which should be invoked to implement method `has()`.
     * The callback signature:
     *
     * ```
     * function (\Psr\Container\ContainerInterface $container, string $id): bool
     * ```
     *
     * @param callable|null $callback PHP callback.
     * @return static self reference.
     */
    public function setCallbackForHas(?callable $callback): self
    {
        $this->callbackForHas = $callback;

        return $this;
    }

    /**
     * Calls the named method which is not a class method.
     * Do not call this method. This is a PHP magic method that forwards calls to the wrapped container.
     *
     * @param string $name the method name.
     * @param array $parameters method parameters.
     * @return mixed the method return value.
     */
    public function __call($name, $parameters)
    {
        return call_user_func_array([$this->container, $name], $parameters);
    }

    /**
     * Returns a property value.
     * Do not call this method. This is a PHP magic method that forwards property resolution to the wrapped container.
     *
     * @param string $name the property name.
     * @return mixed the property value.
     */
    public function __get($name)
    {
        return $this->container->$name;
    }

    /**
     * Sets a property value.
     * Do not call this method. This is a PHP magic method that forwards property resolution to the wrapped container.
     *
     * @param string $name the property name.
     * @param mixed $value the property value.
     */
    public function __set($name, $value)
    {
        $this->container->$name = $value;
    }

    /**
     * Checks if a property value is null.
     * Do not call this method. This is a PHP magic method that forwards property resolution to the wrapped container.
     *
     * @param string $name the property name.
     * @return bool whether property it `null`.
     */
    public function __isset($name)
    {
        return isset($this->container->$name);
    }

    /**
     * Sets a component property to be null.
     * Do not call this method. This is a PHP magic method that forwards property resolution to the wrapped container.
     *
     * @param string $name the property name.
     */
    public function __unset($name)
    {
        unset($this->container->$name);
    }

    /**
     * Clones wrapped container instance.
     * Do not call this method. This is a PHP magic method that invoked automatically after object has been cloned.
     *
     * @since 1.0.1
     */
    public function __clone()
    {
        $this->container = clone $this->container;
    }

    /**
     * Creates new self instance.
     * This method can be useful when writing chain methods calls.
     *
     * @param mixed ...$args constructor arguments.
     * @return static new self instance.
     */
    public static function new(...$args): self
    {
        return new static(...$args);
    }
}