<?php
namespace Worldpay\Payments\Controller\Apm;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;

class Pending extends Apm
{
    public function execute()
    {
        $this->messageManager->addErrorMessage("Payment pending");
        $this->_redirect('checkout/cart');
    }
}
