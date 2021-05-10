<?php

namespace Magenest\Worldpay\Controller\Threeds;

use Magento\Framework\Controller\ResultFactory;

class Process extends Threeds
{
    public function execute()
    {
        $post = $this->getRequest()->getParams();

        if (!isset($post['PaRes'])) {
            throw new \Exception('No PaRes found');
        }
        $paRes = $post['PaRes'];

        $incrementId = $this->checkoutSession->getLastRealOrderId();

        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);

        // First authorise 3ds order
        try {
            $this->wordpayPaymentsCard->authorise3DSOrder($paRes, $order);
        } catch (\Exception $e) {
            $this->checkoutSession->restoreQuote();
            $this->messageManager->addErrorMessage($e->getMessage());
            $return = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $url = $this->urlBuilder->getUrl('checkout/cart');
            $html = "<script>parent.window.location='$url'</script>";
            return $return->setHeader('Content-Type', 'text/html')->setContents($html);
        }

        $wordpayOrderCode = $order->getPayment()->getAdditionalInformation("worldpayOrderCode");
        $payment = $order->getPayment();


        $worldpayClass = $this->wordpayPaymentsCard->setupWorldpay();

        // Update order
        $wpOrder = $worldpayClass->getOrder($wordpayOrderCode);

        if ($wpOrder['paymentStatus'] == 'AUTHORIZED') {
            $wpOrder['amount'] = $wpOrder['authorizedAmount'];
        }
        $amount = $wpOrder['amount']/100;
        $this->wordpayPaymentsCard->updateOrder($wpOrder['paymentStatus'], $wpOrder['orderCode'], $order, $payment, $amount);

        $this->orderSender->send($order, true);

        $quoteId = $order->getQuoteId();

        $this->checkoutSession->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

        /** @var \Magento\Framework\Controller\Result\Raw $return */
        $return = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $url = $this->urlBuilder->getUrl('checkout/onepage/success');
        $html = "<script>parent.window.location='$url'</script>";
        return $return->setHeader('Content-Type', 'text/html')->setContents($html);
//        return $resultRedirect->setPath('checkout/onepage/success');
    }
}
