<?php

namespace Worldpay\Payments\Model\Methods;



class Paypal extends WorldpayPayments {

	protected $_code = 'worldpay_payments_paypal';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
	protected $_formBlockType = 'worldpay/payment_paypalForm';
    protected $_isGateway = true;
}

