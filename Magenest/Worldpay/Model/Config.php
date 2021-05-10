<?php
namespace Magenest\Worldpay\Model;

use Magento\Store\Model\ScopeInterface;

class Config
{
    const STATUS_CANCELLED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_EXPIRED = 2;

    protected $_scopeConfigInterface;
    protected $customerSession;
    protected $_serializer;

    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magenest\Worldpay\Model\Serializer $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_scopeConfigInterface = $configInterface;
        $this->customerSession = $customerSession;
        $this->sessionQuote = $sessionQuote;
        $this->_serializer = $serializer;
        $this->storeManager = $storeManager;
    }

    public function isLiveMode()
    {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/mode',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId()) == 'live_mode';
    }

    public function isAuthorizeOnly()
    {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/payment_action',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId()) == 'authorize';
    }

    public function saveCard()
    {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/save_card') && ($this->customerSession->isLoggedIn() || $this->sessionQuote->getCustomerId());
    }

    public function threeDSEnabled()
    {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/threeds_enabled',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }

    public function getClientKey()
    {
        if ($this->isLiveMode()) {
            return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/live_client_key',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
        } else {
            return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/test_client_key',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
        }
    }

    public function getServiceKey()
    {
        if ($this->isLiveMode()) {
            return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/live_service_key',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
        } else {
            return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/test_service_key',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
        }
    }

    public function getSettlementCurrency()
    {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/settlement_currency',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }

    public function debugMode($code)
    {
        return !!$this->_scopeConfigInterface->getValue('payment/'. $code .'/debug',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }

    public function getPaymentDescription()
    {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/payment_description',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }

    public function getLanguageCode()
    {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/language_code',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }

    public function getShopCountryCode()
    {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/shop_country_code',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }

    public function getSitecodes()
    {
        $sitecodeConfig = $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/sitecodes',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());

        if ($sitecodeConfig && $sitecodeConfig != "[]") {
            $siteCodes = $this->_serializer->unserialize($sitecodeConfig);
            if (is_array($siteCodes)) {
                return $siteCodes;
            }
        }

        return false;
    }

    public function getCanCreateOrder()
    {
        return $this->_scopeConfigInterface->getValue('payment/worldpay_payments_card/can_create_order',ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }
}
