<?php

namespace yii1tech\di\yii;

use CException;
use Yii;
use yii1tech\di\DI;

/**
 * CreatesControllerViaDI allows dependency injection at the controller constructor level.
 *
 * It analyzes controller's constructor signature and passes entities from the PSR compatible container based on type-hinting.
 *
 * @mixin \CWebApplication
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait CreatesControllerViaDI
{
    /**
     * {@inheritdoc}
     */
    public function createController($route, $owner = null)
    {
        if ($owner === null) {
            $owner = $this;
        }

        if ((array)$route === $route || ($route = trim($route,'/')) === '') {
            $route = $owner->defaultController;
        }
        $caseSensitive = $this->getUrlManager()->caseSensitive;

        $route .= '/';
        while (($pos = strpos($route, '/')) !== false) {
            $id = substr($route, 0, $pos);
            if (!preg_match('/^\w+$/', $id)) {
                return null;
            }
            if (!$caseSensitive) {
                $id = strtolower($id);
            }
            $route = (string)substr($route,$pos+1);
            if (!isset($basePath)) { // first segment
                if (isset($owner->controllerMap[$id])) {
                    return [
                        $this->instantiateController(
                            $owner->controllerMap[$id],
                            $id,
                            $owner === $this ? null : $owner
                        ),
                        $this->parseActionParams($route),
                    ];
                }

                if (($module = $owner->getModule($id)) !== null) {
                    return $this->createController($route, $module);
                }

                $basePath = $owner->getControllerPath();
                $controllerID = '';
            } else {
                $controllerID .= '/';
            }

            $className = ucfirst($id) . 'Controller';
            $classFile = $basePath . DIRECTORY_SEPARATOR . $className . '.php';

            if ($owner->controllerNamespace !== null) {
                $className = $owner->controllerNamespace . '\\' . str_replace('/', '\\', $controllerID) . $className;
            }

            if (is_file($classFile)) {
                if (!class_exists($className, false)) {
                    require ($classFile);
                }
                if (class_exists($className, false) && is_subclass_of($className, 'CController')) {
                    $id[0] = strtolower($id[0]);

                    return [
                        $this->instantiateController(
                            $className,
                            $controllerID . $id,
                            $owner === $this ? null : $owner
                        ),
                        $this->parseActionParams($route),
                    ];
                }
                return null;
            }

            $controllerID .= $id;
            $basePath .= DIRECTORY_SEPARATOR . $id;
        }

        return null;
    }

    /**
     * Creates new controller instance.
     *
     * @param array|string $config controller configuration.
     * @param string $id controller ID.
     * @param \CModule|null $module controller module.
     * @return \CController controller instance.
     */
    protected function instantiateController($config, $id, $module)
    {
        if (is_string($config)) {
            $className = $config;
            $config = [];
        } elseif (isset($config['class'])) {
            $className = $config['class'];
            unset($config['class']);
        } else {
            throw new CException(Yii::t('yii', 'Object configuration must be an array containing a "class" element.'));
        }

        $controller = DI::make($className, [
            'id' => $id,
            'module' => $module,
        ]);

        foreach ($config as $name => $value) {
            $controller->$name = $value;
        }

        return $controller;
    }
}