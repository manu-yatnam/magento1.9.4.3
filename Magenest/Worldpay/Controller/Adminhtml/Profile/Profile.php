<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 20/12/2016
 * Time: 15:58
 */
namespace Magenest\Worldpay\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magenest\Worldpay\Model\ProfileFactory;

abstract class Profile extends Action
{
    protected $_profileFactory;

    protected $_pageFactory;

    protected $_coreRegistry;

    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory,
        ProfileFactory $profileFactory,
        Registry $registry
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_profileFactory = $profileFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_pageFactory->create();
        $resultPage->setActiveMenu('Magenest_Worldpay::profile')
            ->addBreadcrumb(__('Subscription Profiles'), __('Subscription Profiles'));

        $resultPage->getConfig()->getTitle()->set(__('Subscription Profiles'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Worldpay::profile');
    }
}
