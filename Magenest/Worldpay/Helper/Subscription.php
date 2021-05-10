<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 20/12/2016
 * Time: 00:06
 */
namespace Magenest\Worldpay\Helper;

use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Model\QuoteFactory;

class Subscription extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_date;
    protected $customerFactory;
    protected $quoteFactory;
    protected $customerRepo;
    protected $productFactory;

    public function __construct(
        Context $context,
        DateTime $dateTime,
        CustomerFactory $customerFactory,
        QuoteFactory $quoteFactory,
        CustomerRepository $customerRepository,
        ProductFactory $productFactory
    ) {
        $this->_date = $dateTime;
        $this->customerFactory = $customerFactory;
        $this->quoteFactory = $quoteFactory;
        $this->customerRepo = $customerRepository;
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    public function getSubscriptionOptions($item)
    {
        $buyRequest = $item->getBuyRequest();
        $additionalOptions = $buyRequest->getAdditionalOptions();

        if (is_array($additionalOptions)) {
            $profileData = [];

            foreach ($additionalOptions as $key => $option) {
                if ($key == 'Subscription') {
                    $explode = explode(' cycles of ', $option);
                    $remaining_cycles = intval($explode[0]) - 1;
                    $startDate = $this->_date->gmtDate('Y-m-d');

                    $frequency = $explode[1];
                    $explodeFrequency = explode(' ', $frequency);
                    if (intval($explodeFrequency[0]) > 1) {
                        $frequency .= 's';
                    }
                    $nextBilling = date('Y-m-d', strtotime($startDate . '+' . $frequency));

                    $profileData = [
                        'frequency' => $frequency,
                        'remaining_cycles' => $remaining_cycles,
                        'last_billed' => $startDate,
                        'next_billing' => $nextBilling,
                        'total_cycles' => intval($explode[0])
                    ];
                }

                if ($key == 'Start Date') {
                    $profileData['start_date'] = $option;
                }
            }

            return $profileData;
        }

        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Model\Order\Item $item
     * @return int
     */
    public function getQuoteAmountForSubscriptionItem($order, $item)
    {
        $shippingAddress = $order->getShippingAddress();

        $orderData = [
            'currency_id' => $order->getQuoteCurrencyCode(),
            'email' => $order->getCustomerEmail(),
            'items' => [
                [
                    'product_id' => $item->getProduct()->getId(),
                    'qty' => $item->getQtyOrdered(),
                    'price' => $item->getPrice()
                ]
            ]
        ];

        if ($shippingAddress) {
            $streetString = '';
            $street = $shippingAddress->getStreet();
            if (isset($street[0])) {
                $streetString .= $street[0];
            }
            if (isset($street[1])) {
                $streetString .= $street[1];
            }

            $orderData['shipping_address'] = [
                'firstname' => $shippingAddress->getFirstname(), //address Details
                'lastname' => $shippingAddress->getLastname(),
                'street' => $streetString,
                'city' => $shippingAddress->getCity(),
                'country_id' => $shippingAddress->getCity(),
                'region' => $shippingAddress->getRegion(),
                'postcode' => $shippingAddress->getPostcode(),
                'telephone' => $shippingAddress->getTelephone(),
                'fax' => $shippingAddress->getFax(),
                'save_in_address_book' => 1
            ];
        }

        $store = $order->getStore();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $websiteId = $store->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']); // load customer by email address

        $quote = $this->quoteFactory->create();
        $quote->setStore($store);
        $quote->setQuoteCurrencyCode($order->getOrderCurrencyCode());

        if (!$customer->getEntityId()) {
            // customer not exist
            $quote->setCustomerEmail($order->getCustomerEmail());
            $quote->setCustomerFirstname($order->getCustomerFirstname());
            $quote->setCustomerGender($order->getCustomerGender());
            $quote->setCustomerLastname($order->getCustomerLastname());
            $quote->setCustomerMiddlename($order->getCustomerMiddlename());
            $quote->setCustomerIsGuest($order->getCustomerIsGuest());
            $quote->setCustomerPrefix($order->getCustomerPrefix());
            $quote->setCustomerSuffix($order->getCustomerSuffix());
        } else {
            $customer= $this->customerRepo->getById($customer->getEntityId());
            $quote->assignCustomer($customer); //Assign quote to customer
        }

        //add items in quote
//        foreach ($orderData['items'] as $item) {
//            $product = $this->productFactory->create()->load($item['product_id']);
//            $product->setPrice($item['price']);
//            $quote->addProduct(
//                $product,
//                intval($item['qty'])
//            );
//        }

        //Set Address to quote
        $billingData =  $order->getBillingAddress()->getData();
        $quote->getBillingAddress()->addData($billingData);
        $paymentMethod = $order->getPayment()->getMethod();

        // Collect Rates and Set Shipping & Payment Method
        if ($shippingAddress) {
            $shippingData = $order->getShippingAddress()->getData();
            $quote->getShippingAddress()->addData($shippingData);

            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($order->getShippingAddress()->getShippingMethod()); //shipping method
        }

        $quote->setInventoryProcessed(false);
        $quote->save(); //Now Save quote and your quote is ready

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        return $quote->getGrandTotal();
    }
}
