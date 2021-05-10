<?php

namespace Magenest\Worldpay\Plugin;

/**
 * Class CsrfValidatorSkip
 * @package Magenest\Worldpay\Plugin
 */
class CsrfValidatorSkip
{
    /**
     * @param $subject
     * @param \Closure $proceed
     * @param $request
     * @param $action
     */
    public function aroundValidate(
        $subject,
        \Closure $proceed,
        $request,
        $action
    ) {
        if ($request->getModuleName() == 'worldpay') {
            return;
        }
        $proceed($request, $action);
    }
}