<?php

namespace Worldpay\Payments\Controller\Saved;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\Session;
use Magento\Framework\View\LayoutFactory;

class Index extends Saved
{
    /**
     * @var Session
     */
    protected $_modelSession;

    /**
     * @var LayoutFactory
     */
    protected $_viewLayoutFactory;

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        if ($navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('worldpay/payments/saved/index');
        }
        $resultPage->getConfig()->getTitle()->set(__('My Saved Cards'));
        return $resultPage;
    }
}
