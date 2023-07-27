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
     * @var array<string, object> dictionary of instances.
     */
    private $instances = [];
    /**
     * @var array<string, callable> dictionary of instance definition callbacks.
     */
    private $definitions = [];
    /**
     * @var array<string, callable> dictionary of instance factory callbacks.
     */
    private $factories = [];
    /**
     * @var array<string, callable> dictionary of instance auto-wire declarations.
     */
    private $autowires = [];
    /**
     * @var array<string, array> dictionary of instance Yii-style configurations.
     */
    private $configs = [];
    /**
     * @var array<string, string> list of entity IDs, which currently in resolution.
     * Used to track circular dependencies.
     */
    private $resolving = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $id)
    {
        if (isset($this->resolving[$id])) {
            throw new CircularDependencyException($this->resolving);
        }

        $this->resolving[$id] = $id;

        $entity = $this->resolve($id);

        unset($this->resolving[$id]);

        return $entity;
    }

    /**
     * Resolves stored entity.
     *
     * @param string $id entity ID.
     * @return mixed resolved entity.
     */
    protected function resolve(string $id)
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

        if (isset($this->autowires[$id])) {
            $this->instances[$id] = $this->getInjector()->make($this, $this->autowires[$id]);
            unset($this->autowires[$id]);

            return $this->instances[$id];
        }

        if (isset($this->configs[$id])) {
            $config = $this->configs[$id];

            if (!isset($config['class'])) {
                $config['class'] = $id;
            }

            $object = \YiiBase::createComponent($config);
            if ($object instanceof \IApplicationComponent) {
                $object->init();
            }

            $this->instances[$id] = $object;

            unset($this->configs[$id]);

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

        if (isset($this->autowires[$id])) {
            return true;
        }

        if (isset($this->configs[$id])) {
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
     * Returns injector instance used for internal dependency resolution.
     *
     * @return \yii1tech\di\InjectorContract internal injector instance.
     */
    protected function getInjector(): InjectorContract
    {
        return new Injector();
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

    /**
     * Specifies binding as a class name, which instance should be automatically resolved based on constructor
     * arguments type-hinting, using this container as their source.
     * For example:
     *
     * ```php
     * class DbCache implements ICache
     * {
     *     public function __construct(CDbConnection $db)
     *     {
     *         // ...
     *     }
     * }
     *
     * $container->lazy(CDbConnection::class, function () {
     *     // ...
     * });
     *
     * $container->autowire(ICache::class, DbCache::class);
     * ```
     *
     * > Note: simplicity of this method approach comes with reduced performance, its usage is not recommended.
     *
     * @param string $id identifier of the entry.
     * @param string|null $class actual entry class, if not set entry ID will be used.
     * @return static self reference.
     */
    public function autowire(string $id, ?string $class = null): self
    {
        $this->autowires[$id] = $class ?? $id;

        return $this;
    }

    /**
     * Specifies binding as Yii-style component configuration.
     * If bound entity is {@see \IApplicationComponent} instance, its `init()` method will be invoked automatically.
     * This method provides easy way of moving existing component declarations from Service Locator to DI Container.
     * For example:
     *
     * ```php
     * $container->config(CDbConnection::class, [
     *     'connectionString' => 'sqlite::memory:',
     * ]);
     * ```
     *
     * @param string $id identifier of the entry.
     * @param array<string, mixed> $config object configuration.
     * @return static self reference.
     */
    public function config(string $id, array $config = []): self
    {
        $this->configs[$id] = $config;

        return $this;
    }
}