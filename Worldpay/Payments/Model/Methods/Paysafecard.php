<?php
namespace Worldpay\Payments\Model\Methods;

class Paysafecard extends WorldpayPayments {

	protected $_code = 'worldpay_payments_paysafecard';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
	protected $_formBlockType = 'worldpay/payment_paysafecardForm';
    protected $_isGateway = true;
}
