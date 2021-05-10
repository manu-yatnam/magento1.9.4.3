<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 15/12/2016
 * Time: 00:58
 */
namespace Magenest\Worldpay\Model;

use Magenest\Worldpay\Model\ResourceModel\Profile as Resource;
use Magenest\Worldpay\Model\ResourceModel\Profile\Collection as Collection;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order;

/**
 * Class Profile
 * @package Magenest\Worldpay\Model
 */
class Profile extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serialize;

    /**
     * Profile constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Resource $resource
     * @param Collection $resourceCollection
     * @param OrderFactory $orderFactory
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Resource $resource,
        Collection $resourceCollection,
        OrderFactory $orderFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        $data = []
    ) {
        $this->orderFactory = $orderFactory;
        $this->serialize = $serialize;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function placeOrder()
    {
        /** @var \Magento\Sales\Model\Order $newOrder */
        $newOrder = $this->generateOrder();
        $newOrder->save();

        $this->addSequenceOrder($newOrder->getId());

        return $newOrder;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function sendInvoice($order)
    {
        $payment = $order->getPayment();
        $payment->setTransactionId($this->getData('subscription_id'))->setIsTransactionClosed(0);
        $payment->registerCaptureNotification($order->getGrandTotal());
        $invoice = $payment->getCreatedInvoice();

        $order->save();
    }

    /**
     * @return Order|null
     */
    public function generateOrder()
    {
        //the order id of first related order of the subscription
        $orderId = $this->getData('order_id');

        if ($orderId) {
            /** @var  \Magento\Sales\Model\Order $order */
            $order = $this->orderFactory->create()->load($orderId);
            $newOrder = $this->orderFactory->create();
            $orderInfo = $order->getData();
            try {
                $objectManager = ObjectManager::getInstance();

                $billingAdd = $objectManager->create('Magento\Sales\Model\Order\Address');
                $oriBA  = $order->getBillingAddress()->getData();
                $billingAdd->setData($oriBA)->setId(null);

                $shippingAdd =  $objectManager->create('Magento\Sales\Model\Order\Address');
                $shippingInfo =$order->getBillingAddress()->getData();
                $shippingAdd->setData($shippingInfo)->setId(null);

                /** @var \Magento\Sales\Model\Order\Payment $payment */
                $payment =  $objectManager->create('Magento\Sales\Model\Order\Payment');
                $paymentMethodCode = $order->getPayment()->getMethod();

                $payment->setMethod($paymentMethodCode);


                $transferDataKeys = array(
                    'store_id',             'store_name',           'customer_id',          'customer_email',
                    'customer_firstname',   'customer_lastname',    'customer_middlename',  'customer_prefix',
                    'customer_suffix',      'customer_taxvat',      'customer_gender',      'customer_is_guest',
                    'customer_note_notify', 'customer_group_id',    'customer_note',        'shipping_method',
                    'shipping_description', 'base_currency_code',   'global_currency_code', 'order_currency_code',
                    'store_currency_code',  'base_to_global_rate',  'base_to_order_rate',   'store_to_base_rate',
                    'store_to_order_rate'
                );


                foreach ($transferDataKeys as $key) {
                    if (isset($orderInfo[$key])) {
                        $newOrder->setData($key, $orderInfo[$key]);
                    } elseif (isset($shippingInfo[$key])) {
                        $newOrder->setData($key, $shippingInfo[$key]);
                    }
                }

                $storeId = $order->getStoreId();
                $newOrder->setStoreId($storeId)
                    ->setState(Order::STATE_NEW)
                    ->setStatus('pending')
                    ->setBaseToOrderRate($order->getBaseToOrderRate())
                    ->setStoreToOrderRate($order->getStoreToOrderRate())
                    ->setOrderCurrencyCode($order->getOrderCurrencyCode())
                    ->setBaseSubtotal($order->getBaseSubtotal())
                    ->setSubtotal($order->getSubtotal())
                    ->setBaseShippingAmount($order->getBaseShippingAmount())
                    ->setShippingAmount($order->getShippingAmount())
                    ->setBaseTaxAmount($order->getBaseTaxAmount())
                    ->setTaxAmount($order->getTaxAmount())
                    ->setBaseGrandTotal($order->getGrandTotal())
                    ->setGrandTotal($order->getGrandTotal())
                    ->setIsVirtual($order->getIsVirtual())
                    ->setWeight($order->getWeight())
                    ->setTotalQtyOrdered($this->getInfoValue('order_info', 'items_qty'))
                    ->setTotalQtyOrdered($order->getTotalQtyOrdered())
                    ->setBillingAddress($billingAdd)
                    ->setShippingAddress($shippingAdd)
                    ->setPayment($payment);

                //todo
                /** @var \Magento\Sales\Model\Order\Item[] $items */
                $items = $order->getAllItems();
                foreach ($items as $item) {
                    $newOrderItem = clone $item;
                    $newOrderItem->setId(null);
                    $newOrder->addItem($newOrderItem);
                }

            } catch (\Exception $e) {

            }
            return $newOrder;
        }

        return null;
    }

    /**
     * @param $orderId
     * @throws \Exception
     */
    public function addSequenceOrder($orderId)
    {
        $sequenceOrderIds = '';
        $sequenceOrderIds = $this->getData('sequence_order_ids');

        if (!$sequenceOrderIds) {
            $this->addData([
                "sequence_order_ids" => $this->serialize->serialize([$orderId])
            ])->save();
        } else {
            if (!empty($sequenceOrderIds)) {
                $sequenceOrderIds = $this->serialize->unserialize($sequenceOrderIds);
                array_push($sequenceOrderIds, $orderId);

                $this->addData([
                    "sequence_order_ids" => $this->serialize->serialize($sequenceOrderIds)
                ])->save();
            }
        }
    }
}
