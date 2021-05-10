<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 20/12/2016
 * Time: 21:34
 */
namespace Magenest\Worldpay\Controller\Adminhtml\Profile;

use Magenest\Worldpay\Controller\Adminhtml\Profile\Profile;

class View extends Profile
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            $profile = $this->_profileFactory->create()->load($id);
            $this->_coreRegistry->register('worldpay_profile_model', $profile);
            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->_initAction();
            $title = __('View Profile');
            $resultPage->getConfig()->getTitle()->prepend($title);

            return $resultPage;
        } else {
            $this->messageManager->addError(__('This profile no longer exists.'));

            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }
    }
}
