<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 29/12/2016
 * Time: 09:35
 */
namespace Magenest\Worldpay\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action;
use Magenest\Worldpay\Controller\Adminhtml\Profile\Profile;

class Save extends Profile
{
    public function execute()
    {
        $requestData = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $profile_id = $requestData['profile_id'];

        if ($requestData) {
            /** @var \Magenest\Worldpay\Model\Profile $model */
            $model = $this->_profileFactory->create()->load($profile_id);

            $model->addData([
                'status' => $requestData['status_select'],
                'total_cycles' => $requestData['input_total_cycles'],
                'remaining_cycles' => $requestData['input_remaining_cycles']
            ]);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Profile has been saved.'));
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/view', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e, __('Something went wrong while saving the profile.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                return $resultRedirect->setPath('*/*/view', ['id' => $this->getRequest()->getParam('profile_id')]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
