<?php
namespace Worldpay\Payments\Model\Methods;

class Przelewy24 extends WorldpayPayments {

	protected $_code = 'worldpay_payments_przelewy24';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
	protected $_formBlockType = 'worldpay/payment_p24Form';
    protected $_isGateway = true;
}
