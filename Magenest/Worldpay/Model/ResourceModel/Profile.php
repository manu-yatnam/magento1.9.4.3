<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 15/12/2016
 * Time: 00:58
 */
namespace Magenest\Worldpay\Model\ResourceModel;

class Profile extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magenest_worldpay_subscription_profiles', 'id');
    }
}
