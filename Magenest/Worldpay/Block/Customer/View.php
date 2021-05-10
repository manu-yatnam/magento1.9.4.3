<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 24/12/2016
 * Time: 10:46
 */
namespace Magenest\Worldpay\Block\Customer;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Registry;
use Magenest\Worldpay\Model\ProfileFactory;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\ObjectManagerInterface;

class View extends \Magento\Framework\View\Element\Template
{
    protected $_profileFactory;

    protected $_coreRegistry;

    protected $_objectManager;

    protected $_customerFactory;

    public function __construct(
        Context $context,
        ProfileFactory $profileFactory,
        ObjectManagerInterface $objectManagerInterface,
        CustomerFactory $customerFactory,
        $data = []
    ) {
        $this->_coreRegistry = $context->getRegistry();
        $this->_profileFactory = $profileFactory;
        $this->_objectManager = $objectManagerInterface;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magenest\Worldpay\Model\Profile
     */
    public function getProfile()
    {
        $id = $this->_coreRegistry->registry('worldpay_customer_view_profile_id');

        $profile = $this->_profileFactory->create()->load($id);

        return $profile;
    }

    public function getProductModel()
    {
        $productId = $this->getProfile()->getData('product_id');
        $model = $this->_objectManager->get('\Magento\Catalog\Model\Product')->load($productId);

        return $model;
    }

    public function getCustomer()
    {
        $customerId = $this->getProfile()->getData('customer_id');

        return $this->_customerFactory->create()->load($customerId);
    }

    public function getCancelUrl()
    {
        $profile = $this->getProfile();
        $profileId = $profile->getProfileId();

        return $this->getUrl('worldpay/customer/cancel', ['profile_id' => $profileId]);
    }
}
