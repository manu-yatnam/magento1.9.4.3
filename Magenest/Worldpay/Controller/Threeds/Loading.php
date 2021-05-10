<?php
namespace Magenest\Worldpay\Controller\Threeds;

use Magento\Framework\Controller\ResultFactory;

class Loading extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    protected $_registry;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_registry = $registry;
        parent::__construct($context);
    }
    public function execute()
    {
        $post = $this->getRequest()->getParams();
        if (!isset($post['PaRes'])) {
            throw new \Exception('No PaRes found');
        }
        $paRes = $post['PaRes'];
        $this->_registry->register('PaRes', $paRes);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        return $resultPage;
    }
}
