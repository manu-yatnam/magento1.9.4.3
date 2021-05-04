<?php

namespace Worldpay\Payments\Controller\Apm;
use Magento\Payment\Helper\Data as PaymentHelper;

abstract class Apm extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    protected $resultJsonFactory;

    protected $orderFactory;
    protected $methods = [];

    protected $wordpayPaymentsCard;

    protected $methodCodes = [
        'worldpay_payments_paypal',
        'worldpay_payments_giropay',
        'worldpay_payments_ideal',
        'worldpay_payments_alipay',
        'worldpay_payments_mistercash',
        'worldpay_payments_przelewy24',
        'worldpay_payments_paysafecard',
        'worldpay_payments_postepay',
        'worldpay_payments_qiwi',
        'worldpay_payments_sofort',
        'worldpay_payments_yandex'
    ];

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Worldpay\Payments\Model\Methods\WorldpayPayments $Worldpay,
        PaymentHelper $paymentHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        $params = []
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
        $this->wordpayPaymentsCard = $paymentHelper->getMethodInstance('worldpay_payments_card');
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
}
