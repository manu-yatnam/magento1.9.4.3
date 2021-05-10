<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 10/08/2016
 * Time: 08:22
 */

namespace Magenest\Worldpay\Observer\Layout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Cart implements ObserverInterface
{
    protected $_cart;
    protected $_serializer;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magenest\Worldpay\Model\Serializer $serializer
    ) {
        $this->_cart = $cart;
        $this->_serializer = $serializer;
    }

    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $buyInfo = $item->getBuyRequest();

        $addedItems = $this->_cart->getQuote()->getAllItems();
        $flag = $this->isSubscriptionItems($addedItems);
        if ($flag && (count($addedItems) > 1)) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Item with subscription option can be purchased standalone only"));
        }

        if ($options = $buyInfo->getAdditionalOptions()) {
            $additionalOptions = [];
            foreach ($options as $key => $value) {
                if ($value) {
                    $additionalOptions[] = array(
                        'label' => $key,
                        'value' => $value
                    );
                }
            }

            $item->addOption(array(
                'code' => 'additional_options',
                'value' => $this->_serializer->serialize($additionalOptions)
            ));
        }
    }

    protected function isSubscriptionItems($items)
    {
        if (!is_array($items)) {
            $items = [$items];
        }
        foreach ($items as $item) {
            $buyInfo = $item->getBuyRequest();
            $options = $buyInfo->getAdditionalOptions();
            if (is_array($options) && key_exists('Subscription', $options)) {
                return true;
            }
        }
        return false;
    }
}
