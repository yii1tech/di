<?php

namespace yii1tech\di;

use Psr\Container\NotFoundExceptionInterface;

/**
 * DefinitionNotFoundException indicates that particular requested ID is missing within the PSR container.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class DefinitionNotFoundException extends \LogicException implements NotFoundExceptionInterface
{
}
