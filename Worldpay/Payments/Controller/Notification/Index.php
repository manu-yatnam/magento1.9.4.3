<?php

namespace Worldpay\Payments\Controller\Notification;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\PaymentFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var OrderFactory
     */
    protected $_modelOrderFactory;

    /**
     * @var PaymentFactory
     */
    protected $_orderPaymentFactory;

    /**
     * @var Worldpay Payment Class
     */
    protected $wpPayment;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        OrderFactory $modelOrderFactory, 
        PaymentFactory $orderPaymentFactory,
        \Worldpay\Payments\Model\Methods\WorldpayPayments $Worldpay)
    {
        $this->_modelOrderFactory = $modelOrderFactory;
        $this->_orderPaymentFactory = $orderPaymentFactory;
        $this->wpPayment = $Worldpay;
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            echo '[NOK]';
            return;
        }

        $worldpayClass = $this->wpPayment->setupWorldpay();

        try {
            $response = file_get_contents('php://input');
            $originalNotification = json_decode($response, true);
        }
        catch(\Exception $e) {
            echo '[NOK]';
            return;
        }

        try {
            $notification = $worldpayClass->getOrder($originalNotification['orderCode']);
        }
        catch(\Worldpay\WorldpayException $e) {
            print_r($e->getMessage());
            echo '[NOK]';
            return;
        }

        if ($originalNotification['paymentStatus'] != $notification['paymentStatus']) {
            echo '[NOK]';
            return;
        }

        //Get order by quote id, add payment as success
        $order = $this->_modelOrderFactory->create()->loadByIncrementId($notification['customerOrderCode']);
        
        $payment = $order->getPayment();

        if (!$payment) {
            echo '[NOK]';
            return;
        }

        if (!$payment->getId()) {
            $payment = $this->_orderPaymentFactory->create()->getCollection()
            ->addAttributeToFilter('last_trans_id', ['eq' => $notification['orderCode']])->getFirstItem();
            $order = $this->_modelOrderFactory->create()
                                ->load($payment->getParentId(), 'entity_id');
        }

        if (!$payment->getId()) {
            echo '[NOK]';
            return;
        }

        if (!$order) {
            http_response_code(500);
            echo '[NOK]';
            return;
        }
        $amount = false;

        if ($notification['amount']) {
            $amount = $notification['amount']/100;
        }

        $this->wpPayment->updateOrder($notification['paymentStatus'], $notification['orderCode'], $order, $payment, $amount);
        
        // give worldpay confirmation
        echo '[OK]';
    }
}
