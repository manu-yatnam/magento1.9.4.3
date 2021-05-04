<?php
namespace Worldpay\Payments\Model;

class Config
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigInterface;
    protected $customerSession;

    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $configInterface,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Backend\Model\Session\Quote $sessionQuote
    )
    {
        $this->_scopeConfigInterface = $configInterface;
        $this->customerSession = $customerSession;
        $this->sessionQuote = $sessionQuote;
    }

    public function isLiveMode() {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/mode') == 'live_mode';
    }

    public function isAuthorizeOnly() {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/payment_action') == 'authorize';
    }

    public function saveCard() {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/save_card') && ($this->customerSession->isLoggedIn() || $this->sessionQuote->getCustomerId());
    }

    public function threeDSEnabled() {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/threeds_enabled');
    }

    public function getClientKey()
    {
        if ($this->isLiveMode()) {
            return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/live_client_key');
        } else {
            return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/test_client_key');
        }
    }

    public function getServiceKey()
    {
        if ($this->isLiveMode()) {
            return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/live_service_key');
        } else {
            return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/test_service_key');
        }
    }

    public function getSettlementCurrency() {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/settlement_currency');
    }

    public function debugMode($code) {
        return !!$this->_scopeConfigInterface->getValue('payment/'. $code .'/debug');
    }

    public function getPaymentDescription() {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/payment_description');
    }

    public function getLanguageCode() {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/language_code');
    }

    public function getShopCountryCode() {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/shop_country_code');
    }

    public function getSitecodes() {
        $sitecodeConfig = $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/sitecodes');
        if ($sitecodeConfig) {
            $siteCodes = $sitecodeConfig;
            if (is_array($siteCodes)) {
                return $siteCodes;
            }
        }
        return false;
    }
}