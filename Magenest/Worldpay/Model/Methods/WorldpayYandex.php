<?php
namespace Magenest\Worldpay\Model\Methods;

class WorldpayYandex extends WorldpayPayments
{
    protected $_code = 'worldpay_payments_yandex';
    protected $_canUseInternal = false;
    protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_formBlockType = 'worldpay/payment_yandexForm';
    protected $_isGateway = true;
}
