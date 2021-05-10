<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 29/12/2016
 * Time: 10:34
 */
namespace Magenest\Worldpay\Model;

use Psr\Log\LoggerInterface;
use Magento\Framework\ObjectManagerInterface;

class ConfigurableProductPlugin
{
    public function aroundAssignProductToOption(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject,
        callable $proceed,
        $optionProduct,
        $option,
        $product
    ) {
        if ($optionProduct) {
            $option->setProduct($optionProduct);
        }
        return $this;
    }
}
