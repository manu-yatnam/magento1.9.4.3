<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 24/12/2016
 * Time: 13:59
 */
namespace Magenest\Worldpay\Controller\Customer;

use Magenest\Worldpay\Model\Config;
use Magenest\Worldpay\Model\ProfileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Cancel extends Action
{
    protected $_profileFactory;

    protected $_resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        ProfileFactory $profileFactory
    ) {
        $this->_resultPageFactory = $pageFactory;
        $this->_profileFactory = $profileFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $profile_id = $this->getRequest()->getParam('profile_id');

        try {
            $this->_profileFactory->create()->load($profile_id)->setData('status', Config::STATUS_CANCELLED);
            $this->messageManager->addSuccessMessage(
                __('The profile\'s status has been changed.')
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to cancel Express Checkout'));
        }


        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('worldpay/customer/profile');
    }
}
