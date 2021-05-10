<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 12/08/2016
 * Time: 22:35
 */
namespace Magenest\Worldpay\Controller\Adminhtml\Profile;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magenest\Worldpay\Model\ProfileFactory;

class MassDelete extends Action
{
    protected $_filter;

    protected $_profileFactory;

    protected $_logger;

    public function __construct(
        Filter $filter,
        ProfileFactory $profileFactory,
        Action\Context $context
    ) {
        $this->_filter = $filter;
        $this->_profileFactory = $profileFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_profileFactory->create()->getCollection());
        $profileDeleted = 0;
        foreach ($collection->getItems() as $profile) {
            $profile->delete();
            $profileDeleted++;
        }
        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been deleted.', $profileDeleted)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
