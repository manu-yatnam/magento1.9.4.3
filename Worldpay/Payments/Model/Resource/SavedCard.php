<?php

namespace Worldpay\Payments\Model\Resource;
 
class SavedCard extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('worldpay_payment', 'id');
    }
}