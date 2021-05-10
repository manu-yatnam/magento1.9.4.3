<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 15/12/2016
 * Time: 10:50
 */
namespace Magenest\Worldpay\Model\Cron;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magenest\Worldpay\Model\Config;
use Magenest\Worldpay\Model\Methods\WorldpayPayments;
use Psr\Log\LoggerInterface;

/**
 * Class Profile
 * @package Magenest\Worldpay\Model\Cron
 */
class Profile
{
    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var \Magenest\Worldpay\Model\ProfileFactory
     */
    protected $_profileFactory;

    /**
     * @var WorldpayPayments
     */
    protected $_worldpayPayments;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serialize;

    /**
     * Profile constructor.
     * @param DateTime $dateTime
     * @param \Magenest\Worldpay\Model\ProfileFactory $profileFactory
     * @param WorldpayPayments $worldpayPayments
     * @param LoggerInterface $logger
     * @param Config $config
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     */
    public function __construct(
        DateTime $dateTime,
        \Magenest\Worldpay\Model\ProfileFactory $profileFactory,
        WorldpayPayments $worldpayPayments,
        LoggerInterface $logger,
        Config $config,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize
    ) {
        $this->_date = $dateTime;
        $this->_profileFactory = $profileFactory;
        $this->_worldpayPayments = $worldpayPayments;
        $this->_logger = $logger;
        $this->config = $config;
        $this->serialize = $serialize;
    }

    /**
     * Execute cron job hourly to check for recurring payments
     */
    public function execute()
    {
        $today = $this->_date->gmtDate('Y-m-d');
        $worldpay = $this->_worldpayPayments->setupWorldpay();

        $profileModel = $this->_profileFactory->create();
        $profiles = $profileModel->getCollection();

        foreach ($profiles as $profile) {
            /** @var \Magenest\Worldpay\Model\Profile $profile */
            $nextBilling = $profile->getData('next_billing');

            // if today is the next billing day
            if ($today == $nextBilling) {
                $orderDetails = $this->serialize->unserialize($profile->getData('additional_data'));
                if ($orderDetails) {
                    $createOrderRequest = [
                        'token' => $profile->getData('token'),
                        'orderDescription' => $orderDetails['orderDescription'],
                        'amount' => ($profile->getData('amount')) * 100,
                        'currencyCode' => $orderDetails['currencyCode'],
                        'siteCode' => $orderDetails['siteCode'],
                        'name' => $orderDetails['name'],
                        'orderType' => 'RECURRING',
                        'billingAddress' => $orderDetails['billingAddress'],
                        'deliveryAddress' => $orderDetails['deliveryAddress'],
                        'customerOrderCode' => $profile->getData('order_id'),
                        'settlementCurrency' => $orderDetails['settlementCurrency'],
                        'shopperIpAddress' => $orderDetails['shopperIpAddress'],
                        'shopperSessionId' => $orderDetails['shopperSessionId'],
                        'shopperUserAgent' => $orderDetails['shopperUserAgent'],
                        'shopperAcceptHeader' => $orderDetails['shopperAcceptHeader'],
                        'shopperEmailAddress' => $orderDetails['shopperEmailAddress']
                    ];
                }

                $remaining_cycles = $profile->getData('remaining_cycles');
                $status = $profile->getData('status');

                // if the profile is active and either permanent or not expired
                if (($remaining_cycles == -1 || $remaining_cycles > 0) && $status == Config::STATUS_ACTIVE) {
                    if (isset($createOrderRequest)) {
                        $response = $worldpay->createApmOrder($createOrderRequest);

                        if ($response['paymentStatus'] === 'SUCCESS') {
                            // Save subscription frequency detail
                            if ($remaining_cycles > 0) {
                                $remaining_cycles--;
                                $nextBilling = date('Y-m-d', strtotime($today . '+' . $profile->getData('frequency')));
                                $profile->addData([
                                    'remaining_cycles' => $remaining_cycles,
                                    'last_billed' => $today,
                                    'next_billing' => $nextBilling
                                ])->save();
                            }
                            // Save new order
                            $order = $profile->placeOrder();

                            $payment = $order->getPayment();
                            $payment->setTransactionId($profile->getData('profile_id'))
                                ->setIsTransactionClosed(0);
                            $order->save();

                            $grossAmount = $order->getGrandTotal();
                            $payment->registerCaptureNotification($grossAmount);
                            $order->save();

                            $invoice = $payment->getCreatedInvoice();
                            if ($invoice) {
                                // notify customer
                                $message = __('Notified customer about invoice #%s.', $invoice->getIncrementId());
                                $order->queueNewOrderEmail()->addStatusHistoryComment($message)
                                    ->setIsCustomerNotified(true)
                                    ->save();
                            }
                        } elseif ($response['paymentStatus'] == 'PRE_AUTHORIZED') {
                            $this->_logger->addDebug('Pre authorized');
                        } else {
                            if (isset($response['paymentStatusReason'])) {
                                throw new \Exception($response['paymentStatusReason']);
                            } else {
                                throw new \Exception(print_r($response, true));
                            }
                        }
                    }
                } else {
                    $profile->addData(['status' => Config::STATUS_EXPIRED])->save();
                }
            }
        }
    }
}
