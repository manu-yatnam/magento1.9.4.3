<?php
namespace Magenest\Worldpay\Block;

class SavedCards extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'Magenest_Worldpay::saved_cards.phtml';

    protected $worldpayPaymentsCard;

    protected $config;

    protected $urlBuilder;

    protected $savedCardFactory;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magenest\Worldpay\Model\Config $config,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Payment\Helper\Data $paymentHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->worldpayPaymentsCard = $paymentHelper->getMethodInstance('worldpay_payments_card');
    }

    public function getClientKey()
    {
        return $this->config->getClientKey();
    }

    public function getSavedCards()
    {
        return $this->worldpayPaymentsCard->getSavedCards();
    }

    public function saveCardEnabled()
    {
        return $this->config->saveCard();
    }

    public function getDeleteUrl($token)
    {
        return $this->urlBuilder->getUrl('worldpay/saved/remove', ['_secure' => true, 'id' => $token]);
    }
}
