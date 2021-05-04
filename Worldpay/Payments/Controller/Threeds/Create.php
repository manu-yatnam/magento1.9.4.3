<?php

namespace Worldpay\Payments\Controller\Threeds;

class Create extends Threeds
{
    public function execute()
    {
        $paymentToken = $this->getRequest()->getParams();
        $result = $this->resultJsonFactory->create();

        if (empty($paymentToken['token'])) {
            return $result->setData([
                'error' => "Create 3DS did not receive payment token"
            ]);
        }

        try {
            $quote = $this->wordpayPaymentsCard->readyMagentoQuote();
            $response = $this->wordpayPaymentsCard->createThreedsOrder($paymentToken['token'], $quote);
        }
        catch(\Exception $e) {
            return $result->setData([
                'error' => $e->getMessage()
            ]);
        }
        try {
            $order = $this->wordpayPaymentsCard->createMagentoOrder($quote);
        }
        catch(\Exception $e) {
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            exit;
        }

        $orderCode = $quote->getPayment()->getAdditionalInformation("worldpayOrderCode");

        $this->checkoutSession->start();

        $this->checkoutSession->clearHelperData();

        $this->checkoutSession
             ->setLastOrderId($order->getId())
             ->setLastRealOrderId($order->getIncrementId())
             ->setLastOrderStatus($order->getStatus());

        if ($response['paymentStatus'] !== 'PRE_AUTHORIZED') {

            $order->addStatusHistoryComment(
                __('No 3ds available for Order Code #%1.', $orderCode)
            )->setIsCustomerNotified(false)->save();

            $quoteId = $order->getQuoteId();
            
            $this->checkoutSession->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            return $result->setData([
                'success' => true,
                'skipThreeDS' => true,
                'redirectURL' => $this->urlBuilder->getUrl('checkout/onepage/success', ['_secure' => true])
            ]);
           
        }

        $order->addStatusHistoryComment(
            __('Redirecting user to 3ds with Worldpay Order Code #%1.', $orderCode)
        )->setIsCustomerNotified(false)->save();

        return $result->setData([
            'success' => true,
            'url' => $response['redirectURL'],
            'oneTime3DsToken' => $response['oneTime3DsToken'],
            'redirectURL' => $this->urlBuilder->getUrl('worldpay/threeds/process', ['_secure' => true])
        ]);
    }
}
