<?php

namespace yii1tech\di\web;

/**
 * {@inheritdoc}
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class InlineAction extends \CInlineAction
{
    use RunsActionWithParamsViaDI;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->runWithParams([]);
    }
}