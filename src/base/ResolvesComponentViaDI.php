<?php

namespace yii1tech\di\base;

use IApplicationComponent;
use yii1tech\di\DI;

/**
 * ResolvesComponentViaDI provides ability to get module/application component instance from the DI container.
 *
 * It also allows usage of arbitrary (without implementation of {@see \IApplicationComponent}) classes for the components.
 *
 * @mixin \CModule
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait ResolvesComponentViaDI
{
    /**
     * @var array<string, object> initialized components dictionary.
     */
    private $_diComponents = [];

    /**
     * {@inheritdoc}
     */
    public function getComponent($id, $createIfNull = true)
    {
        if (isset($this->_diComponents[$id])) {
            return $this->_diComponents[$id];
        }

        $rawComponent = parent::getComponent($id, false);

        if (!$createIfNull || is_object($rawComponent)) {
            return $rawComponent;
        }

        $config = $this->getComponentConfig($id);

        if (is_string($config)) {
            $type = $rawComponent;
            $config = [];
        } elseif (isset($config['class'])) {
            $type = $config['class'];
            unset($config['class']);
        } else {
            return parent::getComponent($id, $createIfNull);
        }

        if (DI::container()->has($type)) {
            $component = DI::container()->get($type);

            foreach ($config as $key => $value) {
                $component->$key = $value;
            }

            if ($component instanceof IApplicationComponent) {
                if (!$component->getIsInitialized()) {
                    $component->init();
                }
            }

            $this->_diComponents[$id] = $component;

            return $component;
        }

        return parent::getComponent($id, $createIfNull);
    }

    /**
     * Returns component configuration.
     * Works only if component has not been initialized yet.
     *
     * @param string $id component ID.
     * @return array|null component configuration.
     */
    private function getComponentConfig($id): ?array
    {
        $config = parent::getComponents(false)[$id] ?? null;

        if ($config === null) {
            return null;
        }

        if (!is_array($config)) {
            $config = [
                'class' => $config,
            ];
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getComponents($loadedOnly = true): array
    {
        if ($loadedOnly) {
            return array_merge($this->_diComponents, parent::getComponents($loadedOnly));
        }

        return parent::getComponents($loadedOnly);
    }

    /**
     * {@inheritdoc}
     */
    public function hasComponent($id): bool
    {
        return isset($this->_diComponents[$id]) || parent::hasComponent($id);
    }

    /**
     * {@inheritdoc}
     */
    public function setComponent($id, $component, $merge = true): void
    {
        if (is_object($component) && !$component instanceof IApplicationComponent) {
            $this->_diComponents[$id] = $component;

            return;
        }

        if (isset($this->_diComponents[$id])) {
            unset($this->_diComponents[$id]);
        }

        parent::setComponent($id, $component, $merge);
    }
}