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
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Magenest\Worldpay\Model\ProfileFactory;

class MassStatus extends Action
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
        $collections = $this->_filter->getCollection($this->_profileFactory->create()->getCollection());
        $status          = (int) $this->getRequest()->getParam('status');
        $totals          = 0;
        try {
            foreach ($collections as $item) {
                /*
                    * @var \Magenest\ShopByBrand\Model\Brand $item
                 */
                $item->setStatus($status)->save();
                $totals++;
            }

            $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', $totals));
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
