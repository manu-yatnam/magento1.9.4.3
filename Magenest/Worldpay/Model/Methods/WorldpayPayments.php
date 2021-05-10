<?php

namespace Magenest\Worldpay\Model\Methods;

use Magenest\Worldpay\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;

class WorldpayPayments extends AbstractMethod
{
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $backendAuthSession;
    protected $config;
    protected $cart;
    protected $urlBuilder;
    protected $_objectManager;
    protected $invoiceSender;
    protected $transactionFactory;
    protected $customerSession;
    protected $savedCardFactory;
    protected $checkoutSession;
    protected $checkoutData;
    protected $quoteRepository;
    protected $quoteManagement;
    protected $orderSender;
    protected $sessionQuote;

    protected $subscriptionHelper;
    protected $profileFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serialize;

    public function __construct(
        \Magenest\Worldpay\Helper\Subscription $subscriptionHelper,
        \Magenest\Worldpay\Model\ProfileFactory $profileFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magenest\Worldpay\Model\Config $config,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magenest\Worldpay\Model\ResourceModel\SavedCard\CollectionFactory $savedCardFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magenest\Worldpay\Logger\Logger $wpLogger,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->urlBuilder = $urlBuilder;
        $this->backendAuthSession = $backendAuthSession;
        $this->config = $config;
        $this->cart = $cart;
        $this->_objectManager = $objectManager;
        $this->invoiceSender = $invoiceSender;
        $this->transactionFactory = $transactionFactory;
        $this->customerSession = $customerSession;
        $this->savedCardFactory = $savedCardFactory;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutData = $checkoutData;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->orderSender = $orderSender;
        $this->sessionQuote = $sessionQuote;
        $this->logger = $wpLogger;
        $this->order = $order;

        $this->subscriptionHelper = $subscriptionHelper;
        $this->profileFactory = $profileFactory;
        $this->serialize = $serialize;
    }

    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    public function getOrderPlaceRedirectUrl()
    {
        return $this->urlBuilder->getUrl('worldpay/apm/redirect', ['_secure' => true]);
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $_tmpData = $data->_data;
        if (isset($_tmpData['additional_data'])) {
            $_serializedAdditionalData = $this->serialize->serialize($_tmpData['additional_data']);
            $_serializedAdditionalData = $this->serialize->unserialize($_serializedAdditionalData);
            $_paymentToken = $_serializedAdditionalData['paymentToken'];
            $infoInstance = $this->getInfoInstance();
            $infoInstance->setAdditionalInformation('payment_token', $_paymentToken);
        }
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     * @throws \Exception
     */
    public function createApmOrder($quote)
    {
        $orderId = $quote->getReservedOrderId();
        $payment = $quote->getPayment();
        $token = $payment->getAdditionalInformation('payment_token');
        $amount = $quote->getGrandTotal();

        $worldpay = $this->setupWorldpay();

        $currency_code = $quote->getQuoteCurrencyCode();

        $orderDetails = $this->getSharedOrderDetails($quote, $currency_code);

        try {
            $createOrderRequest = [
                'token' => $token,
                'orderDescription' => $orderDetails['orderDescription'],
                'amount' => $amount * 100,
                'currencyCode' => $orderDetails['currencyCode'],
                'siteCode' => $orderDetails['siteCode'],
                'name' => $orderDetails['name'],
                'billingAddress' => $orderDetails['billingAddress'],
                'deliveryAddress' => $orderDetails['deliveryAddress'],
                'customerOrderCode' => $orderId,
                'settlementCurrency' => $orderDetails['settlementCurrency'],
                'successUrl' => $this->urlBuilder->getUrl('worldpay/apm/success', ['_secure' => true]),
                'pendingUrl' => $this->urlBuilder->getUrl('worldpay/apm/pending', ['_secure' => true]),
                'failureUrl' => $this->urlBuilder->getUrl('worldpay/apm/failure', ['_secure' => true]),
                'cancelUrl' => $this->urlBuilder->getUrl('worldpay/apm/cancel', ['_secure' => true]),
                'shopperIpAddress' => $orderDetails['shopperIpAddress'],
                'shopperSessionId' => $orderDetails['shopperSessionId'],
                'shopperUserAgent' => $orderDetails['shopperUserAgent'],
                'shopperAcceptHeader' => $orderDetails['shopperAcceptHeader'],
                'shopperEmailAddress' => $orderDetails['shopperEmailAddress']
            ];
            $this->_debug('Order Request: ' . print_r($createOrderRequest, true));
            $response = $worldpay->createApmOrder($createOrderRequest);
            $this->_debug('Order Response: ' . print_r($response, true));

            if ($response['paymentStatus'] === 'SUCCESS') {
                $this->_debug('Order Request: ' . $response['orderCode'] . ' SUCCESS');
                $payment->setIsTransactionClosed(false)
                    ->setTransactionId($response['orderCode'])
                    ->setShouldCloseParentTransaction(false);
                if ($payment->isCaptureFinal($amount)) {
                    $payment->setShouldCloseParentTransaction(true);
                }

                // Add subscription profile
                $quoteItems = $quote->getItems();
                foreach ($quoteItems as $quoteItem) {
                    /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
                    $subscriptionOptions = $this->subscriptionHelper->getSubscriptionOptions($quoteItem);

                    if (is_array($subscriptionOptions)) {
                        $profile = $this->profileFactory->create();
                        $profile->addData([
                            'product_id' => $quoteItem->getProduct()->getId(),
                            'order_id' => $orderId,
                            'customer_id' => $quote->getCustomerId(),
                            'token' => $token,
                            'status' => Config::STATUS_ACTIVE,
                            'amount' => $amount,
                            'currency' => $orderDetails['currencyCode'],
                            'total_cycles' => $subscriptionOptions['total_cycles'],
                            'frequency' => $subscriptionOptions['frequency'],
                            'remaining_cycles' => $subscriptionOptions['remaining_cycles'],
                            'start_date' => $subscriptionOptions['start_date'],
                            'last_billed' => $subscriptionOptions['last_billed'],
                            'next_billing' => $subscriptionOptions['next_billing'],
                            'additional_data' => $this->serialize->serialize($orderDetails)
                        ])->save();
                    }
                }
                // End subscription profile
            } elseif ($response['paymentStatus'] == 'PRE_AUTHORIZED') {
                $this->_debug('Order Request: ' . $response['orderCode'] . ' PRE_AUTHORIZED');
                $payment->setAmount($amount);
                $payment->setAdditionalInformation("worldpayOrderCode", $response['orderCode']);
                $payment->setLastTransId($orderId);
                $payment->setTransactionId($response['orderCode']);
                $payment->setIsTransactionClosed(false);
                $payment->setCcTransId($response['orderCode']);
                $payment->save();
                return $response['redirectURL'];
            } else {
                if (isset($response['paymentStatusReason'])) {
                    throw new \Exception($response['paymentStatusReason']);
                } else {
                    throw new \Exception(print_r($response, true));
                }
            }
        } catch (\Exception $e) {

            $payment->setStatus(self::STATUS_ERROR);
            $payment->setAmount($amount);
            $payment->setLastTransId($orderId);
            $this->_debug($e->getMessage());
            throw new \Exception('Payment failed, please try again later ' . $e->getMessage());
        }
        return false;
    }

    public function isTokenAllowed()
    {
        return true;
    }

    public function capture(InfoInterface $payment, $amount)
    {
        return $this;
    }

    public function authorize(InfoInterface $payment, $amount)
    {
        return $this;
    }

    public function setupWorldpay()
    {
        $service_key = $this->config->getServiceKey();
        $worldpay = new \Worldpay\Worldpay($service_key);

        $worldpay->setPluginData('Magento2', '2.0.25');
        \Worldpay\Utils::setThreeDSShopperObject([
            'shopperIpAddress' => \Worldpay\Utils::getClientIp(),
            'shopperSessionId' => $this->getShopperSessionId(),
            'shopperUserAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "undefined",
            'shopperAcceptHeader' => '*/*'
        ]);
        return $worldpay;
    }

    public function refund(InfoInterface $payment, $amount)
    {
        if ($order = $payment->getOrder()) {
            $worldpay = $this->setupWorldpay();
            try {
                $grandTotal = $order->getGrandTotal();
                $a = $payment->getAdditionalInformation("worldpayOrderCode");
                if ($grandTotal == $amount) {
                    $worldpay->refundOrder($payment->getAdditionalInformation("worldpayOrderCode"));
                } else {
                    $worldpay->refundOrder($payment->getAdditionalInformation("worldpayOrderCode"), $amount * 100);
                }
                return $this;
            } catch (\Exception $e) {
                $a = $e->getMessage();
                throw new LocalizedException(__('Refund failed ' . $e->getMessage()));
            }
        }
    }

    public function void(InfoInterface $payment)
    {
        $worldpayOrderCode = $payment->getAdditionalInformation('worldpayOrderCode');
        $worldpay = $this->setupWorldpay();
        if ($worldpayOrderCode) {
            try {
                $worldpay->cancelAuthorizedOrder($worldpayOrderCode);
            } catch (\Exception $e) {
                throw new LocalizedException(__('Void failed, please try again later ' . $e->getMessage()));
            }
        }
        return true;
    }

    public function cancel(InfoInterface $payment)
    {
        throw new LocalizedException(__('You cannot cancel an APM order'));
    }

    public function updateOrder($status, $orderCode, $order, $payment, $amount)
    {
        if ($status === 'REFUNDED' || $status === 'SENT_FOR_REFUND') {
            $payment
                ->setTransactionId($orderCode)
                ->setParentTransactionId($orderCode)
                ->setIsTransactionClosed(true)
                ->registerRefundNotification($amount);

            $this->_debug('Order: ' . $orderCode . ' REFUNDED');
        } elseif ($status === 'FAILED') {

            $order->cancel()->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true, 'Gateway has declined the payment.')->save();
            $payment->setStatus(self::STATUS_DECLINED);

            $this->_debug('Order: ' . $orderCode . ' FAILED');
        } elseif ($status === 'SETTLED') {
            $this->_debug('Order: ' . $orderCode . ' SETTLED');
        } elseif ($status === 'AUTHORIZED') {
            $payment
                ->setTransactionId($orderCode)
                ->setShouldCloseParentTransaction(1)
                ->setIsTransactionClosed(0)
                ->registerAuthorizationNotification($amount, true);
            $this->_debug('Order: ' . $orderCode . ' AUTHORIZED');
        } elseif ($status === 'SUCCESS') {
            if ($order->canInvoice()) {
                $payment
                    ->setTransactionId($orderCode)
                    ->setShouldCloseParentTransaction(1)
                    ->setIsTransactionClosed(0);

                $invoice = $order->prepareInvoice();
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();

                $transaction = $this->transactionFactory->create();

                $transaction->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();

                $this->invoiceSender->send($invoice, false);
                $order->addStatusHistoryComment(
                    __('Notified customer about invoice #%1.', $invoice->getId())
                )
                    ->setIsCustomerNotified(true);
            }
            $this->_debug('Order: ' . $orderCode . ' SUCCESS');
        } else {
            // Unknown status
            $order->addStatusHistoryComment('Unknown Worldpay Payment Status: ' . $status . ' for ' . $orderCode)
                ->setIsCustomerNotified(true);
        }
        $order->save();
    }

    private function getCheckoutMethod($quote)
    {
        if ($this->customerSession->isLoggedIn()) {
            return \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER;
        }
        if (!$quote->getCheckoutMethod()) {
            if ($this->checkoutData->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $quote->getCheckoutMethod();
    }

    public function readyMagentoQuote()
    {
        $quote = $this->checkoutSession->getQuote();

        $quote->reserveOrderId();
        $this->quoteRepository->save($quote);
        if ($this->getCheckoutMethod($quote) == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $quote->setCustomerId(null)
                ->setCustomerEmail($quote->getBillingAddress()->getEmail())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        }

        $quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$quote->getIsVirtual()) {
            $quote->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$quote->getBillingAddress()->getEmail()
            ) {
                $quote->getBillingAddress()->setSameAsBilling(1);
            }
        }

        $quote->collectTotals();

        return $quote;
    }

    public function createMagentoOrder($quote)
    {
        try {
            $order = $this->quoteManagement->submit($quote);
            return $order;
        } catch (\Exception $e) {
            $orderId = $quote->getReservedOrderId();
            $payment = $quote->getPayment();
            $token = $payment->getAdditionalInformation('payment_token');
            $amount = $quote->getGrandTotal();
            $payment->setStatus(self::STATUS_ERROR);
            $payment->setAmount($amount);
            $payment->setLastTransId($orderId);
            $this->_debug($e->getMessage());

            $this->_objectManager->get('\Magento\Checkout\Model\Session')->restoreQuote();

            throw new \Exception($e->getMessage());
        }
    }

    public function sendMagentoOrder($order)
    {
        $this->checkoutSession->start();

        $this->checkoutSession->clearHelperData();

        $this->checkoutSession->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());
    }

    protected function _debug($debugData)
    {
        if ($this->config->debugMode($this->_code)) {
            $this->logger->debug($debugData);
        }
    }

    protected function getSharedOrderDetails($quote, $currencyCode)
    {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->getShippingAddress();

        $data = [];

        $data['orderDescription'] = $this->config->getPaymentDescription();

        if (!$data['orderDescription']) {
            $data['orderDescription'] = "Magento 2 Order";
        }

        $data['currencyCode'] = $currencyCode;
        $data['name'] = $billing->getName();

        $data['billingAddress'] = [
            "address1" => $billing->getStreetLine(1),
            "address2" => $billing->getStreetLine(2),
            "address3" => $billing->getStreetLine(3),
            "postalCode" => $billing->getPostcode(),
            "city" => $billing->getCity(),
            "state" => "",
            "countryCode" => $billing->getCountryId(),
            "telephoneNumber" => $billing->getTelephone()
        ];

        if ($shipping) {
            $data['deliveryAddress'] = [
                "firstName" => $shipping->getFirstname(),
                "lastName" => $shipping->getLastname(),
                "address1" => $shipping->getStreetLine(1),
                "address2" => $shipping->getStreetLine(2),
                "address3" => $shipping->getStreetLine(3),
                "postalCode" => $shipping->getPostcode(),
                "city" => $shipping->getCity(),
                "state" => "",
                "countryCode" => $shipping->getCountryId(),
                "telephoneNumber" => $shipping->getTelephone()
            ];
        } else {
            $data['deliveryAddress'] = [];
        }


        $data['shopperIpAddress'] = \Worldpay\Utils::getClientIp();
        $data['shopperSessionId'] = $this->getShopperSessionId();
        $data['shopperUserAgent'] = $_SERVER['HTTP_USER_AGENT'];
        $data['shopperAcceptHeader'] = '*/*';

        if ($this->backendAuthSession->isLoggedIn()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($this->sessionQuote->getCustomerId());
            $data['shopperEmailAddress'] = $customer->getEmail();
        } else {
            $data['shopperEmailAddress'] = $this->customerSession->getCustomer()->getEmail();
        }
        $data['siteCode'] = null;
        $siteCodes = $this->config->getSitecodes();
        if ($siteCodes) {
            foreach ($siteCodes as $siteCode) {
                $data['siteCode'] = $siteCode['site_code'];
                $data['settlementCurrency'] = $siteCode['settlement_currency'];
                break;
            }
        }
        if (!isset($data['settlementCurrency'])) {
            $data['settlementCurrency'] = $this->config->getSettlementCurrency();
        }
        return $data;
    }

    public function getShopperSessionId()
    {
        return $this->customerSession->getSessionId();
    }
}
