<?php

namespace Worldpay\Payments\Model\Methods;

use Magento\Payment\Model\InfoInterface;
use Magento\Framework\Exception\LocalizedException;

class Card extends WorldpayPayments {

    protected $_formBlockType = 'Worldpay\Payments\Block\Form\Card';
    protected $_code = 'worldpay_payments_card';
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc               = false;
    protected $_isInitializeNeeded      = false;
    protected $backendAuthSession;

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $_tmpData = $data->_data;
        $_serializedAdditionalData = serialize($_tmpData['additional_data']);
        $additionalDataRef = $_serializedAdditionalData;
        $additionalDataRef = unserialize($additionalDataRef);
        $_paymentToken = $additionalDataRef['paymentToken'];
        $_saveCard = isset($additionalDataRef['saveCard']) ? $additionalDataRef['saveCard'] : false;
        parent::assignData($data);
        $infoInstance = $this->getInfoInstance();

        $infoInstance->setAdditionalInformation('payment_token', $_paymentToken);
        $infoInstance->setAdditionalInformation('save_card', $_saveCard);
        // If token is persistent save in db
        if($_saveCard && ($this->customerSession->isLoggedIn() || $this->backendAuthSession->isLoggedIn())) {

            if ($this->backendAuthSession->isLoggedIn()) {
                $customerId = $this->sessionQuote->getCustomerId();
            } else {
                $customerId = $this->customerSession->getId();
            }
            
            $token_exists = $this->savedCardFactory->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('token', $_paymentToken)
                ->getFirstItem();

            if (empty($token_exists['token'])) {
                $model = $this->_objectManager->create('Worldpay\Payments\Model\SavedCard');
                $model->setData('customer_id', $customerId);
                $model->setData('token', $_paymentToken);
                $model->save();
            }

        }
        return $this;
    }

    public function getSavedCards() {

        if ($this->backendAuthSession->isLoggedIn()) {
            $customerId = $this->sessionQuote->getCustomerId();
        } else {
            $customerId = $this->customerSession->getId();
        }
        
        $this->sessionQuote->getCustomerId();
        $tokens = $this->savedCardFactory->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->loadData();
        $worldpay = $this->setupWorldpay();
        $storedCards = [];
        foreach ($tokens as $t) {
           try {
                $cardDetails = $worldpay->getStoredCardDetails($t['token']);
            }
            catch (\Exception $e) {
                // Delete expired tokens
                if ($e->getCustomCode() == 'TKN_NOT_FOUND') {
                    $t->delete();
                }
            }  
            if (isset($cardDetails['maskedCardNumber']) && !empty($t->getToken())) {
                 $storedCards[] = [
                    'number' => $cardDetails['maskedCardNumber'],
                    'cardType' => $cardDetails['cardType'],
                    'id' => $t->getId(),
                    'token' => $t->getToken()
                ];
            }
        }
        return $storedCards;
    }

    public function authorize(InfoInterface $payment, $amount)
    {
        if ($payment->getAdditionalInformation("worldpayOrderCode")) {
            return $this;
        }
        $payment->setAdditionalInformation('payment_type', 'authorize');
        $this->createOrder($payment, $amount, true);
    }

    public function capture(InfoInterface $payment, $amount)
    {
        $worldpayOrderCode = $payment->getData('last_trans_id');
        if ($worldpayOrderCode) {
            $worldpay = $this->setupWorldpay();
            try {
                $worldpay->captureAuthorizedOrder($worldpayOrderCode, $amount*100);
                $payment->setAdditionalInformation("worldpayOrderCode", $worldpayOrderCode);
                $payment->setShouldCloseParentTransaction(1)->setIsTransactionClosed(1);
                $this->_debug('Capture Order: ' . $worldpayOrderCode . ' success');
            }
            catch (\Exception $e) {
                $this->_debug('Capture Order: ' . $worldpayOrderCode . ' failed with ' . $e->getMessage());
                throw new LocalizedException(__('Payment failed, please try again later ' . $e->getMessage()));
            }
        } else if (!$payment->getAdditionalInformation("worldpayOrderCode")) {
            $payment->setAdditionalInformation('payment_type', 'capture');
            return $this->createOrder($payment, $amount, false);
        } else {
             if ($this->backendAuthSession->isLoggedIn()) {
                $payment->setAdditionalInformation('payment_type', 'capture');
                return $this->createOrder($payment, $amount, false);
             }
        }
        return $this;
    }

    public function isInitializeNeeded()
    {
        $threeDS = $this->config->threeDSEnabled();

        if ($threeDS && !$this->backendAuthSession->isLoggedIn()) {
            return true;
        } else {
            return false;
        }
    }

    public function createThreedsOrder($token, $quote) {
        $orderId = $quote->getReservedOrderId();
        $payment = $quote->getPayment();
        $amount = $quote->getGrandTotal();
        $currency_code = $quote->getQuoteCurrencyCode();
        $authorizeOnly = $this->config->isAuthorizeOnly();
        return $this->createWorldpayOrder($orderId, $payment, $token, $amount, $currency_code, $authorizeOnly, true, $quote);
    }

    protected function createWorldpayOrder($orderId, $payment, $token, $amount, $currency_code, $authorize, $threeDS, $quote) {
        $worldpay = $this->setupWorldpay();

        $orderDetails = $this->getSharedOrderDetails($quote, $currency_code);

        try {
            $liveMode = $this->config->isLiveMode();

            $orderType = 'ECOM';
            
            if ($this->backendAuthSession->isLoggedIn()) {
                $orderType = 'MOTO';
                $threeDS = false;
            }

            if ($threeDS && !$liveMode && $orderDetails['name'] != 'NO 3DS') {
                $orderDetails['name'] = '3D';
            }

            $createOrderRequest = [
                'token' => $token,
                'orderDescription' => $orderDetails['orderDescription'],
                'amount' => $amount*100,
                'currencyCode' => $orderDetails['currencyCode'],
                'siteCode' => $orderDetails['siteCode'],
                'name' => $orderDetails['name'],
                'orderType' => $orderType,
                'is3DSOrder' => $threeDS,
                'authorizeOnly' => $authorize,
                'billingAddress' => $orderDetails['billingAddress'],
                'deliveryAddress' => $orderDetails['deliveryAddress'],
                'customerOrderCode' => $orderId,
                'settlementCurrency' => $orderDetails['settlementCurrency'],
                'shopperIpAddress' => $orderDetails['shopperIpAddress'],
                'shopperSessionId' => $orderDetails['shopperSessionId'],
                'shopperUserAgent' => $orderDetails['shopperUserAgent'],
                'shopperAcceptHeader' => $orderDetails['shopperAcceptHeader'],
                'shopperEmailAddress' => $orderDetails['shopperEmailAddress']
            ];

            $this->_debug('Order Request ' . print_r($createOrderRequest, true));
            $response = $worldpay->createOrder($createOrderRequest);
            $this->_debug('Order Response '. print_r($response, true));
            
            if ($response['paymentStatus'] === 'SUCCESS') {
                $this->_debug('Order: ' .  $response['orderCode'] . ' SUCCESS');
                $payment->setAmount($amount);
                $payment->setIsTransactionClosed(false)
                    ->setShouldCloseParentTransaction(false)
                    ->setCcTransId($response['orderCode'])
                    ->setLastTransId($response['orderCode'])
                    ->setTransactionId($response['orderCode']);
                $payment->setAdditionalInformation("worldpayOrderCode", $response['orderCode']);
                if (!$response['is3DSOrder']) {
                    if ($payment->isCaptureFinal($amount)) {
                        $payment->setShouldCloseParentTransaction(true);
                    }
                } else {
                    return $response;
                }
            }
            else if ($response['paymentStatus'] == 'AUTHORIZED') {
                $this->_debug('Order: ' .  $response['orderCode'] . ' AUTHORIZED');
                $this->setStore($payment->getOrder()->getStoreId());
                $payment->setStatus(self::STATUS_APPROVED)
                    ->setCcTransId($response['orderCode'])
                    ->setLastTransId($response['orderCode'])
                    ->setTransactionId($response['orderCode'])
                    ->setIsTransactionClosed(false)
                    ->setAmount($amount)
                    ->setShouldCloseParentTransaction(false);
                $payment->setAdditionalInformation("worldpayOrderCode", $response['orderCode']);
                if (!$response['is3DSOrder']) {
                    if ($payment->isCaptureFinal($amount)) {
                        $payment->setShouldCloseParentTransaction(true);
                    }  
                } else {
                    return $response;
                }
            }
            else if ($response['paymentStatus'] == 'PRE_AUTHORIZED' && $response['is3DSOrder']) {

                $this->_debug('Order Request: ' . $response['orderCode']  . ' PRE_AUTHORIZED');
                $payment->setAmount($amount);
                $payment->setAdditionalInformation("worldpayOrderCode", $response['orderCode']);
                $payment->setLastTransId($orderId);
                $payment->setTransactionId($response['orderCode']);
                $payment->setIsTransactionClosed(false);
                $payment->setCcTransId($response['orderCode']);
                $payment->save();
                return $response;
            }
            else {
                if (isset($response['paymentStatusReason'])) {
                    throw new LocalizedException(__($response['paymentStatusReason']));
                } else {
                    throw new LocalizedException(__(print_r($response, true)));
                }
            }
        }
        catch (\Worldpay\WorldpayException $e) {
            $this->_debug($e->getMessage());
            throw new LocalizedException(__('Payment failed, please try again later ' . $e->getMessage()));
        }
    }


    protected function createOrder(InfoInterface $payment, $amount, $authorize) {
        
        $this->_debug('Worldpay Card: Begin create order');

        if ($payment->getOrder()) {
            $orderId = $payment->getOrder()->getIncrementId();
            $order = $payment->getOrder();
        } else  {
           $orderId = $payment->getQuote()->getId();
           $order = $payment->getQuote();
        }

        $infoInstance = $this->getInfoInstance();
        $token = $infoInstance->getAdditionalInformation('payment_token');
        $savedCard = $infoInstance->getAdditionalInformation('saved_card');
        $currency_code = $order->getOrderCurrencyCode();
        
        $this->createWorldpayOrder($orderId, $payment, $token, $amount, $currency_code, $authorize, false, $order);

        return $this;
    }

    public function updateOrder($status, $orderCode, $order, $payment, $amount) {
        parent::updateOrder($status, $orderCode, $order, $payment, $amount);
    }

    public function getGenerateOrder3DSUrl() {
        return $this->urlBuilder->getUrl('worldpay/threeds/create', ['_secure' => true]);
    }
    public function getGenerateOrderUrl() {
        return $this->urlBuilder->getUrl('worldpay/card/create', ['_secure' => true]);
    }

    public function authorise3DSOrder($paRes, $order)
    {
        $wordpayOrderCode = $order->getPayment()->getAdditionalInformation("worldpayOrderCode");
        $worldpay = $this->setupWorldpay();

        if (!$wordpayOrderCode) {
            $this->_debug('No order id found in session!');
            throw new \Exception('Failed - There was a problem authorising your 3DS order');
        }

        $this->_debug('Authorising 3DS Order: ' . $wordpayOrderCode . ' with paRes: ' . $paRes);

        $response = $worldpay->authorize3DSOrder($wordpayOrderCode, $paRes);
        if (isset($response['paymentStatus']) && ($response['paymentStatus'] == 'SUCCESS' || $response['paymentStatus'] == 'AUTHORIZED')) {
           $this->_debug('Order: ' . $wordpayOrderCode . ' 3DS authorised successfully');
           return true;
        } else {
            $this->_debug('Order: ' . $wordpayOrderCode . ' 3DS failed authorising');
            throw new \Exception( (isset($response['paymentStatus']) ? $response['paymentStatus'] : "FAILED") .' - There was a problem authorising your 3DS order');
        }
    }

    public function cancel(InfoInterface $payment)
    {
        $worldpayOrderCode = $payment->getAdditionalInformation('worldpayOrderCode');
        $worldpay = $this->setupWorldpay();
        if ($worldpayOrderCode) {
            try {
                $worldpay->cancelAuthorizedOrder($worldpayOrderCode);
            }
            catch (\Exception $e) {
                throw new LocalizedException(__('Void failed, please try again later ' . $e->getMessage()));
            }
        }
        return true;
    }
}

