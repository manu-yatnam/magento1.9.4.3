<?php
namespace Worldpay\Payments\Controller\Saved;

abstract class Saved extends \Magento\Framework\App\Action\Action
{

	protected $customerSession;
    protected $resultPageFactory;
    protected $customerUrl;
    protected $savedCardFactory;

	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $customerUrl,
        \Worldpay\Payments\Model\Resource\SavedCard\CollectionFactory $savedCardFactory
    ) {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerUrl = $customerUrl;
        $this->savedCardFactory = $savedCardFactory;
        parent::__construct($context);
    }
}
