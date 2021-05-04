<?php
namespace Worldpay\Payments\Controller\Apm;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;

class Cancel extends Apm
{
    public function execute()
    {
        $this->messageManager->addErrorMessage("Payment cancelled");
        $this->_redirect('checkout/cart');
    }
}
