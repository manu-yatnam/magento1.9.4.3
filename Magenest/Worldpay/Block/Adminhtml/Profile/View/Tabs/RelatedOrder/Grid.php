<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 26/03/2016
 * Time: 10:13
 */
namespace Magenest\Worldpay\Block\Adminhtml\Profile\View\Tabs\RelatedOrder;

use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Sales\Model\OrderFactory;

/**
 * Class Grid
 * @package Magenest\Worldpay\Block\Adminhtml\Profile\View\Tabs\RelatedOrder
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serialize;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param Registry $registry
     * @param OrderFactory $orderFactory
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     * @param array $data
     *
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $registry,
        OrderFactory $orderFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_orderFactory = $orderFactory;
        $this->serialize = $serialize;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('order_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $profile = $this->_coreRegistry->registry('worldpay_profile_model');
        $orderId = $profile->getOrderId();
        $sequenceOrderIds = $profile->getData('sequence_order_ids');
        $ids = [];
        if ($sequenceOrderIds) {
            $ids = $this->serialize->unserialize($sequenceOrderIds);
        }
        array_unshift($ids, $orderId);
        $collection = $this->_orderFactory->create()->getCollection()->addFieldToFilter('increment_id', $ids);
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'increment_id',
            [
                'header' => __('ID'),
                'type' => 'text',
                'index' => 'increment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Purchased Date'),
                'index' => 'created_at',
                'type' => 'datetime'
            ]
        );
        $this->addColumn(
            'billing_name',
            [
                'header' => __('Bill-to Name'),
                'index' => 'created_at',
                'type' => 'text'
            ]
        );
        $this->addColumn(
            'shipping_name',
            [
                'header' => __('Ship-to Name'),
                'index' => 'created_at',
                'type' => 'text'
            ]
        );
        $this->addColumn(
            'base_grand_total',
            [
                'header' => __('Grand Total (Base)'),
                'index' => 'base_grand_total',
                'type' => 'text'
            ]
        );
        $this->addColumn(
            'grand_total',
            [
                'header' => __('Grand Total (Purchased)'),
                'index' => 'base_grand_total',
                'type' => 'text'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'base_grand_total',
                'type' => 'text'
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'sales/order/view',
            ['order_id' => $row->getIncrementId()]
        );
    }
}
