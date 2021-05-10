<?php

namespace Magenest\Worldpay\Model\ResourceModel\SavedCard;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magenest\Worldpay\Model\SavedCard', 'Magenest\Worldpay\Model\ResourceModel\SavedCard');
    }
}
