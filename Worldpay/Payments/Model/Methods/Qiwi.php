<?php
namespace Worldpay\Payments\Model\Methods;

class Qiwi extends WorldpayPayments {

	protected $_code = 'worldpay_payments_qiwi';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
	protected $_formBlockType = 'worldpay/payment_qiwiForm';
    protected $_isGateway = true;
}
