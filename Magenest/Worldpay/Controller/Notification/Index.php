<?php

namespace Magenest\Worldpay\Controller\Notification;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\PaymentFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_modelOrderFactory;

    protected $_orderPaymentFactory;

    protected $wpPayment;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        OrderFactory $modelOrderFactory,
        PaymentFactory $orderPaymentFactory,
        \Magenest\Worldpay\Model\Methods\WorldpayPayments $Worldpay
    ) {
        $this->_modelOrderFactory = $modelOrderFactory;
        $this->_orderPaymentFactory = $orderPaymentFactory;
        $this->wpPayment = $Worldpay;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }

        $worldpayClass = $this->wpPayment->setupWorldpay();

        try {
            $response = file_get_contents('php://input');
            $originalNotification = json_decode($response, true);
        } catch (\Exception $e) {
            return;
        }

        try {
            $notification = $worldpayClass->getOrder($originalNotification['orderCode']);
        } catch (\Worldpay\WorldpayException $e) {
            return;
        }

        if ($originalNotification['paymentStatus'] != $notification['paymentStatus']) {
            return;
        }

        //Get order by quote id, add payment as success
        $order = $this->_modelOrderFactory->create()->loadByIncrementId($notification['customerOrderCode']);
        
        $payment = $order->getPayment();

        if (!$payment) {
            return;
        }

        if (!$payment->getId()) {
            $payment = $this->_orderPaymentFactory->create()->getCollection()
            ->addAttributeToFilter('last_trans_id', ['eq' => $notification['orderCode']])->getFirstItem();
            $order = $this->_modelOrderFactory->create()
                                ->load($payment->getParentId(), 'entity_id');
        }

        if (!$payment->getId()) {
            return;
        }

        if (!$order) {
            http_response_code(500);
            return;
        }
        $amount = false;

        if ($notification['amount']) {
            $amount = $notification['amount']/100;
        }

        $this->wpPayment->updateOrder($notification['paymentStatus'], $notification['orderCode'], $order, $payment, $amount);
        
        // give worldpay confirmation
    }
}
