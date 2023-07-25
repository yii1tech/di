<?php

namespace yii1tech\di;

use Psr\Container\NotFoundExceptionInterface;

class DefinitionNotFoundException extends \LogicException implements NotFoundExceptionInterface
{
}
