<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 25/03/2016
 * Time: 23:41
 */
namespace Magenest\Worldpay\Block\Adminhtml\Profile;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container as FormContainer;
use Magento\Framework\Registry;

class View extends FormContainer
{
    protected $_coreRegistry;

    public function __construct(
        Context $context,
        Registry $registry,
        $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Magenest_Worldpay';
        $this->_controller = 'adminhtml_profile';
        parent::_construct();

//        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
//        $this->buttonList->remove('save');

        $this->_mode = 'view';
    }

    public function getHeaderText()
    {
        $model = $this->_coreRegistry->registry('worldpay_profile_model');

        return __("View Profile '%1'", $this->escapeHtml($model->getToken()));
    }
}
