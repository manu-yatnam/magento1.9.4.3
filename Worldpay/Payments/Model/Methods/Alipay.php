<?php
namespace Worldpay\Payments\Model\Methods;

class Alipay extends WorldpayPayments {

	protected $_code = 'worldpay_payments_alipay';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
	protected $_formBlockType = 'worldpay/payment_alipayForm';
    protected $_isGateway = true;
}
