<?php
namespace Magenest\Worldpay\Controller\Apm;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;

class Failure extends Apm
{
    public function execute()
    {$incrementId = $this->checkoutSession->getLastRealOrderId();

        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);

        $quoteId = $order->getQuoteId();
        sleep(1);
        $wordpayOrderCode = $order->getPayment()->getAdditionalInformation("worldpayOrderCode");
        $worldpayClass = $this->wordpayPaymentsCard->setupWorldpay();
        $wpOrder = $worldpayClass->getOrder($wordpayOrderCode);
        $payment = $order->getPayment();
        $amount = $wpOrder['amount']/100;
        $this->wordpayPaymentsCard->updateOrder('FAILED', $wpOrder['orderCode'], $order, $payment, $amount);

        $this->messageManager->addErrorMessage("Failed to process payment, please try again");
        $this->_redirect('checkout/cart');
    }
}
