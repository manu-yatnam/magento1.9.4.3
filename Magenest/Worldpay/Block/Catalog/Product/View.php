<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 14/12/2016
 * Time: 13:30
 */
namespace Magenest\Worldpay\Block\Catalog\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magenest\Worldpay\Model\PlanFactory;

class View extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var PlanFactory
     */
    protected $_planFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serialize;

    /**
     * View constructor.
     * @param Context $context
     * @param DateTime $dateTime
     * @param PlanFactory $planFactory
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     * @param array $data
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        PlanFactory $planFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        array $data = []
    ) {
        $this->_date = $dateTime;
        $this->_planFactory = $planFactory;
        $this->serialize = $serialize;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function getIsSubscriptionProduct()
    {
        $productId = $this->getCurrentProductId();

        $planModel = $this->_planFactory->create();
        $plan = $planModel->getCollection()->addFieldToFilter('product_id', $productId)->getFirstItem();

        if ($plan) {
            $value = $plan->getEnabled();
            return $value;
        }

        return false;
    }

    /**
     * @return array|bool|float|int|mixed|string|null
     */
    public function getSubscriptionOptions()
    {
        $productId = $this->getCurrentProductId();
        $planModel = $this->_planFactory->create();
        $plan = $planModel->getCollection()->addFieldToFilter('product_id', $productId)->getFirstItem();
        if ($plan) {
            $options = $plan->getSubscriptionValue();
            $options = $this->serialize->unserialize($options);
            return $options;
        }
        return '';
    }

    /**
     * @return bool|mixed
     */
    public function getCanUserDefineStartDate()
    {
        $productId = $this->getCurrentProductId();

        $planModel = $this->_planFactory->create();
        $plan = $planModel->getCollection()->addFieldToFilter('product_id', $productId)->getFirstItem();

        if ($plan) {
            $value = $plan->getData('can_define_startdate');
            return $value;
        }

        return false;
    }

    public function getCurrentDate()
    {
        return $this->_date->gmtDate('Y-m-d');
    }

    public function getCurrentProductId()
    {
        $product = $this->_coreRegistry->registry('current_product');
        return $product->getId();
    }
}
