<?php

namespace yii1tech\di;

use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * Injector is able to analyze callable dependencies based on type-hinting and inject them from any PSR-11 compatible container.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Injector implements InjectorContract
{
    /**
     * {@inheritdoc}
     */
    public function invoke(ContainerInterface $container, callable $callable, array $arguments = [])
    {
        $callable = Closure::fromCallable($callable);
        $reflection = new ReflectionFunction($callable);

        return $reflection->invokeArgs($this->resolveDependencies($container, $reflection, $arguments));
    }

    /**
     * {@inheritdoc}
     */
    public function make(ContainerInterface $container, string $class, array $arguments = [])
    {
        $classReflection = new ReflectionClass($class);
        if (!$classReflection->isInstantiable()) {
            throw new InvalidArgumentException("Class '{$class}' is not instantiable.");
        }

        $reflection = $classReflection->getConstructor();
        if ($reflection === null) {
            // Method __construct() does not exist
            return new $class();
        }

        return $classReflection->newInstanceArgs($this->resolveDependencies($container, $reflection, $arguments));
    }

    /**
     * Builds the actual list of arguments to be passed into callback, resolving them from container according to the type-hints.
     *
     * @param \Psr\Container\ContainerInterface $container source PSR compatible container.
     * @param \ReflectionFunctionAbstract $reflection reflection of the callback to be invoked.
     * @param array $arguments manually set arguments.
     * @return array built invocation arguments.
     */
    private function resolveDependencies(ContainerInterface $container, ReflectionFunctionAbstract $reflection, array $arguments = []): array
    {
        $resolvedArguments = [];

        foreach ($reflection->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            $parameterHasType = $parameter->hasType();

            if (array_key_exists($parameterName, $arguments)) {
                $resolvedArguments[$parameterName] = $arguments[$parameterName];

                continue;
            }

            if ($parameterHasType) {
                $parameterType = $parameter->getType();

                $resolvedValue = $this->resolveParameterByType($container, $parameterType);

                if ($resolvedValue !== null) {
                    $resolvedArguments[$parameterName] = $resolvedValue;

                    continue;
                }
            }

            if ($parameter->isDefaultValueAvailable()) {
                $resolvedArguments[$parameterName] = $parameter->getDefaultValue();

                continue;
            }

            if ($parameter->isOptional() && $parameter->allowsNull()) {
                $resolvedArguments[$parameterName] = null;

                continue;
            }

            if ($parameterHasType) {
                throw new DefinitionNotFoundException('Unable to resolve argument "' . $parameterName . '" of type "' . $parameter->getType()->getName() . '"');
            }

            throw new InvalidArgumentException('Unable to resolve argument "' . $parameterName . '" - it has no type-hint.');
        }

        return $resolvedArguments;
    }

    /**
     * Resolves value from PSR compatible container for the particular type-hint.
     *
     * @param \Psr\Container\ContainerInterface $container source PSR compatible container.
     * @param \ReflectionType $type type-hint reflection.
     * @return mixed|null resolved value, `null` - if not found.
     */
    private function resolveParameterByType(ContainerInterface $container, ReflectionType $type)
    {
        if ($type instanceof ReflectionNamedType) {
            if ($container->has($type->getName())) {
                return $container->get($type->getName());
            }

            return null;
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $namedType) {
                if ($container->has($namedType->getName())) {
                    return $container->get($namedType->getName());
                }
            }

            return null;
        }

        if ($type instanceof ReflectionIntersectionType) {
            foreach ($type->getTypes() as $namedType) {
                if ($container->has($namedType->getName())) {
                    return $container->get($namedType->getName());
                }
            }

            return null;
        }

        return null;
    }
}