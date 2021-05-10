<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 14/12/2016
 * Time: 11:40
 */
namespace Magenest\Worldpay\Observer\Product;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class Save
 * @package Magenest\Worldpay\Observer\Product
 */
class Save implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Magenest\Worldpay\Model\PlanFactory
     */
    protected $_planFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serialize;

    /**
     * Save constructor.
     * @param RequestInterface $requestInterface
     * @param \Magenest\Worldpay\Model\PlanFactory $planFactory
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     */
    public function __construct(
        RequestInterface $requestInterface,
        \Magenest\Worldpay\Model\PlanFactory $planFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize
    ) {
        $this->_request = $requestInterface;
        $this->_planFactory = $planFactory;
        $this->serialize = $serialize;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $planModel = $this->_planFactory->create();
        $data = $this->_request->getParams();

        $product = $observer->getProduct();
        $productId = $product->getId();

        $plan = $planModel->getCollection()->addFieldToFilter('product_id', $productId)->getFirstItem();
        if ($plan) {
            $planModel->load($plan->getId());
        }

        $data = $data['event'];
        $planData = [];

        if (array_key_exists('worldpay_payments', $data)) {
            $newData = $data['worldpay_payments']['subscription_options'];
            $result = [];

            if ($newData != 'false') {
                foreach ($newData as $item) {
                    if (array_key_exists('is_delete', $item)) {
                        if ($item['is_delete']) {
                            continue;
                        }
                    }

                    array_push($result, $item);
                }

                $planData = [
                    'product_id' => $productId,
                    'subscription_value' => $this->serialize->serialize($result)
                ];
            }
        }

        $planData['enabled'] = $data['worldpay_payments_enabled']['enable'];
        $planData['can_define_startdate'] = $data['worldpay_payments_enabled']['can_define_startdate'];

        $planModel->addData($planData)->save();
    }
}
