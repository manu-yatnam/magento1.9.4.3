<?php
namespace Worldpay\Payments\Model\Methods;

class Postepay extends WorldpayPayments {

	protected $_code = 'worldpay_payments_postepay';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
	protected $_formBlockType = 'worldpay/payment_postepayForm';
    protected $_isGateway = true;
}
