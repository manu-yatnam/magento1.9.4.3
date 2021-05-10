<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 26/03/2016
 * Time: 10:08
 */
namespace Magenest\Worldpay\Block\Adminhtml\Profile\View\Tabs;

use Magento\Backend\Block\Widget\Grid\Container;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Context;

class RelatedOrder extends Container implements TabInterface
{
    protected $_coreRegistry = null;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->buttonList->remove('add');
        $this->_blockGroup = 'Magenest_Worldpay';
        $this->_controller = 'adminhtml_profile_view_tabs_relatedOrder';
    }

    public function getTabLabel()
    {
        return __("Related Order");
    }

    public function getTabTitle()
    {
        return __("Related Order") ;
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
