<?php

namespace yii1tech\di;

use Psr\Container\ContainerInterface;

/**
 * Container is a basic light-weight PSR compatible container.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Container implements ContainerInterface
{
    /**
     * @var array<string, object> list of instances.
     */
    private $instances = [];

    /**
     * @var array<string, callable> list of instance definition callbacks.
     */
    private $definitions = [];

    /**
     * @var array<string, callable> list of instance factory callbacks.
     */
    private $factories = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $id)
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->factories[$id])) {
            return call_user_func($this->factories[$id], $this);
        }

        if (isset($this->definitions[$id])) {
            $this->instances[$id] = call_user_func($this->definitions[$id], $this);
            unset($this->definitions[$id]);

            return $this->instances[$id];
        }

        if ($id === get_class($this)) {
            return $this;
        }

        if ($id === ContainerInterface::class) {
            return $this;
        }

        throw new DefinitionNotFoundException("Missing DI definition for: {$id}");
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        if (isset($this->instances[$id])) {
            return true;
        }

        if (isset($this->definitions[$id])) {
            return true;
        }

        if (isset($this->factories[$id])) {
            return true;
        }

        if ($id === get_class($this)) {
            return true;
        }

        if ($id === ContainerInterface::class) {
            return true;
        }

        return false;
    }

    /**
     * Binds given ID with exact class instance.
     * For example:
     *
     * ```php
     * $container->instance(ICache::class, new CDummyCache());
     * ```
     *
     * @param string $id identifier of the entry.
     * @param mixed $object entry instance.
     * @return static self reference.
     */
    public function instance(string $id, $object): self
    {
        $this->instances[$id] = $object;

        return $this;
    }

    /**
     * Specifies binding via a callback, which should be resolved in lazy way on entity retrieval.
     * Callback can accept this container instance as a sole argument.
     * For example:
     *
     * ```php
     * $container->lazy(ICache::class, function (ContainerInterface $container) {
     *     $cache = new CDbCache();
     *     $cache->setDbConnection($container->get(CDbConnection::class));
     *     $cache->init();
     *
     *     return $cache;
     * });
     * ```
     *
     * Specified callback will be resolved only once.
     *
     * @param string $id identifier of the entry.
     * @param callable $callable entry resolution callback.
     * @return static self reference.
     */
    public function lazy(string $id, callable $callable): self
    {
        $this->definitions[$id] = $callable;

        return $this;
    }

    /**
     * Specifies binding via a callback, which should be resolved every time on entity retrieval.
     * Callback can accept this container instance as a sole argument.
     * For example:
     *
     * ```php
     * $container->factory('item-find-command', function (ContainerInterface $container) {
     *     $db = $container->get(CDbConnection::class);
     *
     *     return $db->getCommandBuilder()->createFindCommand('items');
     * });
     * ```
     *
     * Specified callback will be resolved on every call of {@see get()}, allowing creating of multiple class instances.
     *
     * @param string $id identifier of the entry.
     * @param callable $callable entry resolution callback.
     * @return static self reference.
     */
    public function factory(string $id, callable $callable): self
    {
        $this->factories[$id] = $callable;

        return $this;
    }
}