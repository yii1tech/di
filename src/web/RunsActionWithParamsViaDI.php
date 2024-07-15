<?php

namespace yii1tech\di\web;

use yii1tech\di\DI;

/**
 * RunsActionWithParamsViaDI allows dependency injection at the action method level.
 *
 * @mixin \CAction
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0.3
 */
trait RunsActionWithParamsViaDI
{
    /**
     * {@inheritdoc}
     */
    protected function runWithParamsInternal($object, $method, $params)
    {
        $arguments = [];
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();

            if ($param->hasType() && !$param->getType()->isBuiltin()) {
                if ($param->getType()->getName() == \CWebUser::class) {
                    $arguments[$name] = \Yii::app()->getUser();

                    continue;
                }

                if ($param->getType()->getName() == \CHttpRequest::class) {
                    $arguments[$name] = \Yii::app()->getRequest();

                    continue;
                }

                if (is_subclass_of($param->getType()->getName(), \CActiveRecord::class) && array_key_exists('id', $params)) {
                    $arguments[$name] = ($param->getType()->getName())::model()->findByPk($params['id']);

                    continue;
                }
                
                if (DI::has($param->getType()->getName())) {
                    $arguments[$name] = DI::get($param->getType()->getName());

                    continue;
                }
            }

            if (isset($params[$name])) {
                if (version_compare(PHP_VERSION, '8.0', '>=')) {
                    $isArray = $param->getType() && $param->getType()->getName() === 'array';
                } else {
                    $isArray = $param->isArray();
                }

                if ($isArray) {
                    $arguments[$name] = is_array($params[$name]) ? $params[$name] : array($params[$name]);
                } elseif (!is_array($params[$name])) {
                    $arguments[$name] = $params[$name];
                } else {
                    return false;
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[$name] = $param->getDefaultValue();
            } else {
                return false;
            }
        }

        $method->invokeArgs($object, $arguments);

        return true;
    }
}
