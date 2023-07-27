<?php

namespace yii1tech\di\base;

/**
 * {@inheritdoc}
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Module extends \CModule
{
    use ResolvesComponentViaDI;
}