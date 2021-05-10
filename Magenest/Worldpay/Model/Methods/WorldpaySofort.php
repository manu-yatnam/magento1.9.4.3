<?php
namespace Magenest\Worldpay\Model\Methods;

class WorldpaySofort extends WorldpayPayments
{
    protected $_code = 'worldpay_payments_sofort';
    protected $_canUseInternal = false;
    protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_formBlockType = 'worldpay/payment_sofortForm';
    protected $_isGateway = true;
}
