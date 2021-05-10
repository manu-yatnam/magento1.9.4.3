<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 14/12/2016
 * Time: 11:12
 */
namespace Magenest\Worldpay\Model\ResourceModel\Plan;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magenest\Worldpay\Model\Plan', 'Magenest\Worldpay\Model\ResourceModel\Plan');
    }
}
