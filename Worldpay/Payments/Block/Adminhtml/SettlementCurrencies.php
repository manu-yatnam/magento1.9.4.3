<?php
namespace Worldpay\Payments\Block\Adminhtml;

class SettlementCurrencies extends \Magento\Framework\View\Element\Html\Select
{

	public function __construct(
    \Magento\Framework\View\Element\Context $context, 
    \Magento\Config\Model\Config\Source\Locale\Currency $currencies, 
    array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currencies = $currencies;
    }

    public function _toHtml() {
        if (!$this->getOptions()) {
            $allCurrencies = $this->currencies->toOptionArray();
            foreach ($allCurrencies as $currency) {
            	$this->addOption($currency['value'], $currency['value']);
            }
        }
        return parent::_toHtml();
    }

    public function setInputName($value) {
        return $this->setName($value);
    }

}