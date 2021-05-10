<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 09/08/2016
 * Time: 09:17
 */
namespace Magenest\Worldpay\Observer\Layout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

// Don't use anymore
class Add implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();
        $product->setHasOptions(true);
    }
}
