<?php
namespace Magenest\Worldpay\Model\Methods;

class WorldpayAlipay extends WorldpayPayments
{

    protected $_code = 'worldpay_payments_alipay';
    protected $_canUseInternal = false;
    protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_formBlockType = 'worldpay/payment_alipayForm';
    protected $_isGateway = true;
}
