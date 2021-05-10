<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 24/12/2016
 * Time: 10:43
 */
namespace Magenest\Worldpay\Controller\Customer;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

class View extends Action
{
    protected $_resultPageFactory;

    protected $_logger;

    protected $_coreRegistry;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        LoggerInterface $loggerInterface,
        Registry $registry
    ) {
        $this->_resultPageFactory = $pageFactory;
        $this->_logger = $loggerInterface;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $this->_coreRegistry->register('worldpay_customer_view_profile_id', $id);
        $profile_id = $this->_objectManager->get('\Magenest\Worldpay\Model\Profile')->load($id)->getToken();

        $this->_view->loadLayout();
        if ($block = $this->_view->getLayout()->getBlock('customer_subscription_account_view')) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Recurring Profile ') . $profile_id);
        $this->_view->renderLayout();
    }
}
