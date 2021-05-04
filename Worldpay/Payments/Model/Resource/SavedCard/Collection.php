<?php

namespace Worldpay\Payments\Model\Resource\SavedCard;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Worldpay\Payments\Model\SavedCard', 'Worldpay\Payments\Model\Resource\SavedCard');
    }
 
    
}