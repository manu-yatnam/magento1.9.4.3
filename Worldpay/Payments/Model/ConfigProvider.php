<?php
namespace Worldpay\Payments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;


class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
        'worldpay_payments_card'
    ];


    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        Config $config
    ) {
        $this->escaper = $escaper;
        $this->config = $config;
        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $outConfig = [];
        $outConfig['payment']['worldpay_payments']['client_key'] = $this->config->getClientKey();
        $outConfig['payment']['worldpay_payments']['save_card'] = $this->config->saveCard();
        // Get saved cards
        if ($this->config->saveCard()) {
            $outConfig['payment']['worldpay_payments']['saved_cards'] = $this->methods['worldpay_payments_card']->getSavedCards();
        }

        $outConfig['payment']['worldpay_payments']['language_code'] = $this->config->getLanguageCode();
        $outConfig['payment']['worldpay_payments']['country_code'] = $this->config->getShopCountryCode();

        $outConfig['payment']['worldpay_payments']['threeds_enabled'] = filter_var($this->config->threeDSEnabled(), FILTER_VALIDATE_BOOLEAN);
        // Get 3ds details
        if ($this->config->threeDSEnabled()) {
             $outConfig['payment']['worldpay_payments']['ajax_generate_order_url'] = $this->methods['worldpay_payments_card']->getGenerateOrder3DSUrl();
        }

        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $outConfig['payment']['worldpay_payments']['redirect_url'] = $this->getMethodRedirectUrl($code);
            }
        }
        return $outConfig;
    }


    public function getMethodRedirectUrl($code)
    {
        return $this->methods[$code]->getOrderPlaceRedirectUrl();
    }
}
