<?php

namespace yii1tech\di;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var array<string, object>
     */
    private $instances = [];

    /**
     * @var array<string, callable>
     */
    private $definitions = [];

    /**
     * @var array<string, callable>
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

        return false;
    }

    public function instance(string $id, $object): self
    {
        $this->instances[$id] = $object;

        return $this;
    }

    public function lazy(string $id, callable $callable): self
    {
        $this->definitions[$id] = $callable;

        return $this;
    }

    public function factory(string $id, callable $callable): self
    {
        $this->factories[$id] = $callable;

        return $this;
    }
}