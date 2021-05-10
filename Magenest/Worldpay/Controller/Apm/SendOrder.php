<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 04/11/2017
 * Time: 07:43
 */
namespace Magenest\Worldpay\Controller\Apm;

use Magenest\Worldpay\Controller\Apm\Apm;

class SendOrder extends Apm
{
    public function execute()
    {
        // Send order
        $incrementId = $this->checkoutSession->getLastRealOrderId();
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        $sent = $this->orderSender->send($order, true);

        sleep(1);
        // Update order
        $wordpayOrderCode = $order->getPayment()->getAdditionalInformation("worldpayOrderCode");
        $worldpayClass = $this->wordpayPaymentsCard->setupWorldpay();
        $wpOrder = $worldpayClass->getOrder($wordpayOrderCode);
        $payment = $order->getPayment();
        $amount = $wpOrder['amount']/100;
        $this->wordpayPaymentsCard->updateOrder($wpOrder['paymentStatus'], $wpOrder['orderCode'], $order, $payment, $amount);

//        $this->checkoutSession->clearQuote()->clearStorage();
    }
}
