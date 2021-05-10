<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 26/03/2016
 * Time: 00:12
 */
namespace Magenest\Worldpay\Block\Adminhtml\Profile\View\Tabs;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\ObjectManagerInterface;
use Magenest\Worldpay\Model\Config;

class Info extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_objectManager;

    protected $_customerFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        ObjectManagerInterface $interface,
        CustomerFactory $customerFactory,
        array $data
    ) {
        $this->_objectManager = $interface;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    public function getProfile()
    {
        /** @var \Magenest\Worldpay\Model\Profile $profile */
        $profile = $this->_coreRegistry->registry('worldpay_profile_model');

        return $profile;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductModel()
    {
        $productId = $this->getProfile()->getData('product_id');
        $model = $this->_objectManager->get('\Magento\Catalog\Model\Product')->load($productId);

        return $model;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        $customerId = $this->getProfile()->getData('customer_id');

        return $this->_customerFactory->create()->load($customerId);
    }

    public function getTabLabel()
    {
        return __('General Profile Information');
    }

    public function getTabTitle()
    {
        return __('General Profile Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
