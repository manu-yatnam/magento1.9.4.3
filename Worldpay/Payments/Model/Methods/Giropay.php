<?php

namespace Worldpay\Payments\Model\Methods;



class Giropay extends WorldpayPayments {

	protected $_code = 'worldpay_payments_giropay';
	protected $_canUseInternal = false;
	protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
	protected $_formBlockType = 'worldpay/payment_giropayForm';
    protected $_isGateway = true;
}

