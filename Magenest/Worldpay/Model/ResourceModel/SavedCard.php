<?php

namespace Magenest\Worldpay\Model\ResourceModel;
 
class SavedCard extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magenest_worldpay_saved_cards', 'id');
    }
}
