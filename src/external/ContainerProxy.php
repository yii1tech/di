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
 * class PhpDiContainerProxy extends ContainerProxy
 * {
 *     public function has(string $id): bool
 *     {
 *         return in_array($id, $this->container->getKnownEntryNames(), true);
 *     }
 * }
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
        return $this->container->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return $this->container->has($id);
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