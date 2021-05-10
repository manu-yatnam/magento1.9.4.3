<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 15/12/2016
 * Time: 00:59
 */
namespace Magenest\Worldpay\Model\ResourceModel\Profile;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('Magenest\Worldpay\Model\Profile', 'Magenest\Worldpay\Model\ResourceModel\Profile');
    }
}
