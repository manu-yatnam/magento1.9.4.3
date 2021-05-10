<?php
namespace Magenest\Worldpay\Model;

use Magenest\Worldpay\Model\ResourceModel\SavedCard as Resource;
use Magenest\Worldpay\Model\ResourceModel\SavedCard\Collection as Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class SavedCard extends \Magento\Framework\Model\AbstractModel
{
    public function __construct(
        Context $context,
        Registry $registry,
        Resource $resource,
        Collection $resourceCollection,
        $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getAvailableCustomerBillingAgreements($customerId)
    {
        $collection = $this->_billingAgreementFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', self::STATUS_ACTIVE)
            ->setOrder('agreement_id');
        return $collection;
    }
}
