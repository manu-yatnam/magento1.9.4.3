<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 20/12/2016
 * Time: 15:56
 */
namespace Magenest\Worldpay\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action;
use Magenest\Worldpay\Controller\Adminhtml\Profile\Profile;

class Index extends Profile
{
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Profiles'));

        return $resultPage;
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Worldpay::profile');
    }
}
