<?php
namespace Worldpay\Payments\Block\Adminhtml;

class Sitecode extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected $currencyRenderer = null;
    protected $settlementCurrencyRenderer = null;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = []
    )
    {
        $this->_elementFactory  = $elementFactory;
        parent::__construct($context,$data);
    }

    protected function getCurrencyRenderer()
    {
        if (!$this->currencyRenderer) {
            $this->currencyRenderer = $this->getLayout()->createBlock(
                'Worldpay\Payments\Block\Adminhtml\Currencies',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->currencyRenderer;
    }

    protected function getSettlementCurrencyRenderer()
    {
        if (!$this->settlementCurrencyRenderer) {
            $this->settlementCurrencyRenderer = $this->getLayout()->createBlock(
                'Worldpay\Payments\Block\Adminhtml\SettlementCurrencies',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->settlementCurrencyRenderer;
    }

    protected function _construct() {
        $this->addColumn('currency', ['label' => __('Acceptance Currency'), 'renderer'  => $this->getCurrencyRenderer()]);
        $this->addColumn('settlement_currency', ['label' => __('Settlement Currency'), 'renderer'  => $this->getSettlementCurrencyRenderer()]);
        $this->addColumn('site_code', ['label' => __('Sitecode')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::_construct();
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row) {
        $options = [];
        $currency = $row->getCurrency();
        if ($currency) {
            $options['option_' . $this->getCurrencyRenderer()->calcOptionHash($currency)] = 'selected="selected"';
        }

        $settlementCurrency = $row->getSettlementCurrency();

        if ($settlementCurrency) {
            $options['option_' . $this->getSettlementCurrencyRenderer()->calcOptionHash($settlementCurrency)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

}