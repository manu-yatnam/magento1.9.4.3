<?php
namespace Worldpay\Payments\Block\Form;

class Card extends \Magento\Payment\Block\Form
{
    /**
     * Purchase order template
     *
     * @var string
     */
    protected $_template = 'Worldpay_Payments::form/card.phtml';
    private $worldpayPaymentsCard;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Worldpay\Payments\Model\Config $config,
        \Magento\Payment\Helper\Data $paymentHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->worldpayPaymentsCard = $paymentHelper->getMethodInstance('worldpay_payments_card');
    }

    public function getClientKey()
    {
        return $this->config->getClientKey();
    }

    public function saveCardEnabled()
    {
        return $this->config->saveCard();
    }

    public function getSavedCards()
    {
        return $this->worldpayPaymentsCard->getSavedCards();
    }
}