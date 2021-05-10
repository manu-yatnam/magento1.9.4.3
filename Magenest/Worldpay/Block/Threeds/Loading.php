<?php
namespace Magenest\Worldpay\Block\Threeds;

class Loading extends \Magento\Framework\View\Element\Template
{
    protected $_registry;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    public function getPaRes()
    {
        return $this->_registry->registry('PaRes');
    }

}
