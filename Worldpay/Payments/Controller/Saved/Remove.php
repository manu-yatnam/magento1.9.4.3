<?php

namespace Worldpay\Payments\Controller\Saved;

class Remove extends Saved
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $customerId = $this->customerSession->getId();

        $tokens = $this->savedCardFactory->create()
                ->addFieldToFilter('token', $id)
                ->addFieldToFilter('customer_id', $customerId)
                ->loadData();
        foreach ($tokens as $t) {
            $t->delete();
        }

        $this->_redirect('worldpay/saved/index');
    }
}
