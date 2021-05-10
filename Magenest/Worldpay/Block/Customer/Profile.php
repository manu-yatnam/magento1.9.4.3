<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 24/12/2016
 * Time: 10:22
 */
namespace Magenest\Worldpay\Block\Customer;

use Magenest\Worldpay\Model\ProfileFactory;
use Magento\Catalog\Block\Product\Context;
use Magento\Customer\Helper\Session\CurrentCustomer;

class Profile extends \Magento\Framework\View\Element\Template
{
    protected $_currentCustomer;

    protected $_profileFactory;

    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        ProfileFactory $profileFactory,
        array $data
    ) {
        $this->_currentCustomer = $currentCustomer;
        $this->_profileFactory = $profileFactory;
        parent::__construct($context, $data);
    }

    public function getCustomerProfiles()
    {
        $customerId = $this->_currentCustomer->getCustomerId();

        $profiles = $this->_profileFactory->create()
            ->getCollection()->addFieldToFilter('customer_id', $customerId);

        return $profiles;
    }

    public function getOrderViewUrl($order_id)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $order_id]);
    }

    public function getProfileViewUrl($id)
    {
        return $this->getUrl('worldpay/customer/view', [
            'id' => $id
        ]);
    }
}
