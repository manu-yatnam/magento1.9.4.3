<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 26/03/2016
 * Time: 00:11
 */
namespace Magenest\Worldpay\Block\Adminhtml\Profile\View;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            ['data' =>
                [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
