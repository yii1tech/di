<?php

namespace yii1tech\di;

use LogicException;
use Psr\Container\ContainerExceptionInterface;

/**
 * CircularDependencyException indicates that circular dependency is detected while resolving entity in the PSR container.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class CircularDependencyException extends LogicException implements ContainerExceptionInterface
{
    private $ids = [];

    /**
     * Constructor.
     *
     * @param string[] $ids sequence of entity IDs, which causes circular dependency.
     * @param \Throwable|null $previous previous exception.
     */
    public function __construct(array $ids, \Throwable $previous = null)
    {
        if (!empty($ids)) {
            $this->ids = array_values($ids);
            $this->ids[] = $this->ids[0];
        }

        $message = 'Circular dependency detected in container: ' . implode('->', $this->ids);

        parent::__construct($message, 0, $previous);
    }

    /**
     * Returns sequence of entity IDs, which causes circular dependency.
     *
     * @return string[] entity IDs.
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}