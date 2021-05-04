<?php

namespace Worldpay\Payments\Controller\Apm;

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Braintree\Model\PaymentMethod\PayPal;

class Redirect extends Apm
{
    public function execute() {
        
        $redirectUrl = false;
        $result = $this->resultJsonFactory->create();
        $quote = $this->checkoutSession->getQuote();
        $code = $quote->getPayment()->getMethod();
        $quote = $this->methods[$code]->readyMagentoQuote();

        try {
            $redirectUrl = $this->methods[$code]->createApmOrder($quote);
        }
        catch(\Exception $e) {
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        try {
            $order = $this->methods[$code]->createMagentoOrder($quote);
        }
        catch(\Exception $e) {
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        if (!$order) {
            return $result->setData([
                'success' => false,
                'error' => 'Error, please try again'
            ]);
        }

        $orderCode = $quote->getPayment()->getAdditionalInformation("worldpayOrderCode");

        $order->addStatusHistoryComment(
            __('Redirecting user with Worldpay Order Code  #%1.', $orderCode)
        )->setIsCustomerNotified(false)->save();

        $this->methods[$code]->sendMagentoOrder($order);

        return $result->setData([
            'success' => true,
            'redirectURL' => $redirectUrl
        ]);
    }
}
