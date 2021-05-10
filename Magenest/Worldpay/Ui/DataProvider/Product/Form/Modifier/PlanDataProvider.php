<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 14/12/2016
 * Time: 10:43
 */
namespace Magenest\Worldpay\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;

/**
 * Class PlanDataProvider
 * @package Magenest\Worldpay\Ui\DataProvider\Product\Form\Modifier
 */
class PlanDataProvider extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Magenest\Worldpay\Model\PlanFactory
     */
    protected $planFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serialize;

    /**
     * PlanDataProvider constructor.
     * @param LocatorInterface $locator
     * @param RequestInterface $request
     * @param \Magenest\Worldpay\Model\PlanFactory $planFactory
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     */
    public function __construct(
        LocatorInterface $locator,
        RequestInterface $request,
        \Magenest\Worldpay\Model\PlanFactory $planFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize
    ) {
        $this->locator = $locator;
        $this->request = $request;
        $this->planFactory = $planFactory;
        $this->serialize = $serialize;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        $productId = $product->getId();

        $planModel = $this->planFactory->create();
        $plan = $planModel->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->getFirstItem()->getData();
        if (!empty($plan)) {
            $isEnabled = $plan->getEnabled();
            $options = $this->serialize->unserialize($plan->getSubscriptionValue());

            $data[strval($productId)]['event']['worldpay_payments_enabled']['enable'] = $isEnabled;
            $data[strval($productId)]['event']['worldpay_payments_enabled']['can_define_startdate'] = $plan->getData('can_define_startdate');

            $data[strval($productId)]['event']['worldpay_payments']['subscription_options'] = $options;
        }
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
