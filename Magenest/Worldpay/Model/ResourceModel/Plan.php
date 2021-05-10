<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 14/12/2016
 * Time: 11:11
 */
namespace Magenest\Worldpay\Model\ResourceModel;

class Plan extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magenest_worldpay_subscription_plans', 'id');
    }
}
