<?php

namespace yii1tech\di\yii;

/**
 * {@inheritdoc}
 */
class WebApplication extends \CWebApplication
{
    use ResolvesComponentViaDI;
    use CreatesControllerViaDI;
}