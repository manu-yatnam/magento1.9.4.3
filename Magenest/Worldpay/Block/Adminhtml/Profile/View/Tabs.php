<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 25/03/2016
 * Time: 23:37
 */
namespace Magenest\Worldpay\Block\Adminhtml\Profile\View;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('profile_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Subscription Profile'));
    }
}
