<?php

namespace Worldpay\Payments\Controller\Apm;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;


class Success extends Apm
{
    public function execute()
    {
        $incrementId = $this->checkoutSession->getLastRealOrderId();
        
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);

        $quoteId = $order->getQuoteId();
        sleep(1);
        $wordpayOrderCode = $order->getPayment()->getAdditionalInformation("worldpayOrderCode");
        $worldpayClass = $this->wordpayPaymentsCard->setupWorldpay();
        $wpOrder = $worldpayClass->getOrder($wordpayOrderCode);
        $payment = $order->getPayment();
        $amount = $wpOrder['amount']/100;
        $this->wordpayPaymentsCard->updateOrder($wpOrder['paymentStatus'], $wpOrder['orderCode'], $order, $payment, $amount);

        // $this->orderSender->send($order);


        $this->checkoutSession->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);
        $this->_redirect('checkout/onepage/success');
    }
}
