<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 24/12/2016
 * Time: 10:19
 */
namespace Magenest\Worldpay\Controller\Customer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;

class Profile extends Action
{
    protected $_customerSession;

    public function __construct(
        Context $context,
        CustomerSession $customerSession
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    public function execute()
    {
        $this->_view->loadLayout();
        if ($block = $this->_view->getLayout()->getBlock('worldpay_customer_profiles_list')) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $this->_view->getPage()->getConfig()->getTitle()->set(__('My Recurring Profiles'));
        $this->_view->renderLayout();
    }
}
