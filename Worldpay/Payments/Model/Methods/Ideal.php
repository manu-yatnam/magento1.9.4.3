<?php
namespace Worldpay\Payments\Model\Methods;

class Ideal extends WorldpayPayments {

	protected $_code = 'worldpay_payments_ideal';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
	protected $_formBlockType = 'worldpay/payment_idealForm';
    protected $_isGateway = true;
}
