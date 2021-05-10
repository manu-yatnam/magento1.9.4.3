<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 14/12/2016
 * Time: 11:10
 */
namespace Magenest\Worldpay\Model;

use Magenest\Worldpay\Model\ResourceModel\Plan as Resource;
use Magenest\Worldpay\Model\ResourceModel\Plan\Collection as Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Plan extends AbstractModel
{
    protected $_eventPrefix = 'plan_';

    public function __construct(
        Context $context,
        Registry $registry,
        Resource $resource,
        Collection $resourceCollection,
        $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
}
