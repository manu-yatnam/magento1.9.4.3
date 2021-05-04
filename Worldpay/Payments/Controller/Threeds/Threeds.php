<?php

namespace Worldpay\Payments\Controller\Threeds;
use Magento\Framework\View\LayoutFactory;
use Magento\Payment\Helper\Data as PaymentHelper;

abstract class Threeds extends \Magento\Framework\App\Action\Action
{

	/**
     * @var Session
     */
    protected $_modelSession;

    /**
     * @var LayoutFactory
     */
    protected $_viewLayoutFactory;


    protected $wordpayPaymentsCard;
    protected $urlBuilder;
    protected $checkoutSession;
    protected $orderFactory;

    protected $orderSender;

	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        LayoutFactory $viewLayoutFactory,
        PaymentHelper $paymentHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    )
    {
        $this->_viewLayoutFactory = $viewLayoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->urlBuilder = $context->getUrl();
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
        $this->wordpayPaymentsCard = $paymentHelper->getMethodInstance('worldpay_payments_card');
         parent::__construct($context);
    }
}
