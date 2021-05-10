<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 29/12/2016
 * Time: 10:19
 */
namespace Magenest\Worldpay\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action;
use Magenest\Worldpay\Controller\Adminhtml\Profile\Profile;

class Delete extends Profile
{
    public function execute()
    {
        $profile_id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();

        $model = $this->_profileFactory->create()->load($profile_id);

        try {
            $model->delete();
            $this->messageManager->addSuccessMessage(__('Profile has been removed.'));

            return $resultRedirect->setPath('*/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e, __('Something went wrong while removing the profile.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
