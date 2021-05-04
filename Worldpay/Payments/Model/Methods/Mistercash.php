<?php
namespace Worldpay\Payments\Model\Methods;

class Mistercash extends WorldpayPayments {

	protected $_code = 'worldpay_payments_mistercash';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
	protected $_formBlockType = 'worldpay/payment_mistercashForm';
    protected $_isGateway = true;
}
