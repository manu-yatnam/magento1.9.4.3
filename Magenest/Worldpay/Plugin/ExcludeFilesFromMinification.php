<?php

namespace Magenest\Worldpay\Plugin;
use Magento\Framework\View\Asset\Minification;
class ExcludeFilesFromMinification
{
    public function aroundGetExcludes(
        \Magento\Framework\View\Asset\Minification $subject,
        callable $proceed,
        $contentType
    ) {
        $result = $proceed($contentType);

        //Content type can be css or js
        if ($contentType == 'js') {
            $result[] = 'https://cdn.worldpay.com/v1/worldpay.js';
        }

        return $result;
    }

}