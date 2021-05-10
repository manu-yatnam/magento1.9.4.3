<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 09/08/2016
 * Time: 09:17
 */
namespace Magenest\Worldpay\Observer\Layout;

use Magento\Framework\Event\ObserverInterface;

class Load implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $fullActionName = $observer->getEvent()->getFullActionName();

        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getEvent()->getLayout();
        $handler = '';
        if ($fullActionName == 'catalog_product_view') {
            $handler = 'worldpay_catalog_product_view';
        }

        if ($handler) {
            $layout->getUpdate()->addHandle($handler);
        }
    }
}
