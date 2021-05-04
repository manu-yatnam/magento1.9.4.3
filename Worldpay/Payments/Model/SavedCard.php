<?php
namespace Worldpay\Payments\Model;

class SavedCard extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Worldpay\Payments\Model\Resource\SavedCard');
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
