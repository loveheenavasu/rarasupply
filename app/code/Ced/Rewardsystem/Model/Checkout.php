<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Rewardsystem
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Rewardsystem\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\AddressRegistry;

/**
 * Class Checkout
 * @package Ced\Rewardsystem\Model
 */
class Checkout
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AddressRegistry
     */
    protected $addressRepository;

    /**
     * @var Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Quote\Model\ShippingMethodManagement
     */
    protected $shippingManagement;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var OrderConfig
     */
    private $config;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $resolver;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $customerRepo;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $idMaskFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Framework\CurrencyInterface
     */
    protected $currency;

    /**
     * @var \Magento\Framework\Locale\Currency
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Ced\Rewardsystem\Helper\Data
     */
    protected $rewardsystemHelper;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $country;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var RegisuserpointFactory
     */
    protected $regisuserpointFactory;

    /**
     * @var \Magento\Quote\Model\QuoteManagementFactory
     */
    protected $quoteManagementFactory;

    /**
     * Checkout constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Model\Cart $cart
     * @param AddressRegistry $addressRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Sales\Model\Order $orderFactory
     * @param \Magento\Quote\Model\ShippingMethodManagement $shippingManagement
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Magento\Sales\Model\Order\Config $config
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Locale\ResolverInterface $resolver
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepo
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $idMaskFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\CurrencyInterface $currency
     * @param \Magento\Framework\Locale\Currency $localeCurrency
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param RuleFactory $ruleFactory
     * @param \Ced\Rewardsystem\Helper\Data $rewardsystemHelper
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Directory\Model\Config\Source\Country $country
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param RegisuserpointFactory $regisuserpointFactory
     * @param \Magento\Quote\Model\QuoteManagementFactory $quoteManagementFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart,
        AddressRegistry $addressRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\Order $orderFactory,
        \Magento\Quote\Model\ShippingMethodManagement $shippingManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Sales\Model\Order\Config $config,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Locale\ResolverInterface $resolver,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepo,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Quote\Model\QuoteIdMaskFactory $idMaskFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\CurrencyInterface $currency,
        \Magento\Framework\Locale\Currency $localeCurrency,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\Rewardsystem\Model\RuleFactory $ruleFactory,
        \Ced\Rewardsystem\Helper\Data $rewardsystemHelper,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory,
        \Magento\Quote\Model\QuoteManagementFactory $quoteManagementFactory,
        array $data = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->addressRepository = $addressRepository;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->orderFactory = $orderFactory;
        $this->shippingManagement = $shippingManagement;
        $this->orderSender = $orderSender;
        $this->orderManagement = $orderManagement;
        $this->config = $config;
        $this->quoteFactory = $quoteFactory;
        $this->customerRepository = $customerRepository;
        $this->resolver = $resolver;
        $this->customerRepo = $customerRepo;
        $this->wishlistFactory = $wishlistFactory;
        $this->idMaskFactory = $idMaskFactory;
        $this->pricingHelper = $pricingHelper;
        $this->directoryHelper = $directoryHelper;
        $this->currency = $currency;
        $this->localeCurrency = $localeCurrency;
        $this->productFactory = $productFactory;
        $this->ruleFactory = $ruleFactory;
        $this->rewardsystemHelper = $rewardsystemHelper;
        $this->paymentHelper = $paymentHelper;
        $this->addressFactory = $addressFactory;
        $this->escaper = $escaper;
        $this->country = $country;
        $this->customerFactory = $customerFactory;
        $this->regisuserpointFactory = $regisuserpointFactory;
        $this->quoteManagementFactory = $quoteManagementFactory;
    }

    /**
     * getCartmobi
     *
     * @param array $data data
     *
     * @return array
     */
    function _getCartmobi($data)
    {
        $customer_id = isset($data['customer_id']) ? $data['customer_id'] : '0';
        if ($customer_id) {
            try {
                $quote = $this->quoteRepository->getForCustomer($customer_id);
                $this->cart->setQuote($quote);
                $this->isLoggedIn = true;
            } catch (NoSuchEntityException $e) {
                $store = $this->storeManager->getStore();
                $quote = $this->quoteFactory->create();
                $quote->setStore($store);
                $customerd = $this->customerRepository->getById($customer_id);
                $quote->setCurrency();
                $quote->assignCustomer($customerd);
                $this->cart->setQuote($quote);
                return ['success' => true, 'cart' => $this->cart];
            } catch (Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        } else {
            $cart_id = isset($data['cart_id']) ? $data['cart_id'] : '0';
            if ($cart_id) {
                try {
                    $quote = $this->quoteRepository->get($cart_id);
                } catch (NoSuchEntityException $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                } catch (Exception $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }
                $this->cart->setQuote($quote);
            }
        }
        if (is_object($this->cart->getQuote()) && $this->cart->getQuote()->getEntityId()) {
            return ['success' => true, 'cart' => $this->cart];
        } else {
            $quote = $this->quoteFactory->create();
            $this->cart->setQuote($quote);
            return ['success' => true, 'cart' => $this->cart];
        }
    }

    /**
     * getCartmobi
     *
     * @param array $data data
     *
     * @return array
     */
    function _getQuotemobi($data)
    {
        $customer_id = isset($data['customer_id']) ? $data['customer_id'] : '0';
        $quote = null;

        if ($customer_id) {
            try {
                $quote = $this->quoteRepository->getForCustomer($customer_id);
                $this->isLoggedIn = true;
                return ['success' => true, 'quote' => $quote];
            } catch (NoSuchEntityException $e) {
                $quote = $this->quoteFactory->create();
                $store = $this->storeManager->getStore();
                $quote->setStore($store);
                $customerd = $this->customerRepository->getById($customer_id);
                $quote->setCurrency();
                $quote->assignCustomer($customerd);
                return ['success' => true, 'quote' => $quote];
            } catch (Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        } else {
            $cart_id = isset($data['cart_id']) ? $data['cart_id'] : '0';
            if ($cart_id) {
                try {
                    $quote = $this->quoteRepository->get($cart_id);
                    return ['success' => true, 'quote' => $quote];
                } catch (NoSuchEntityException $e) {
                    $quote = $this->quoteFactory->create();
                    $this->cart->setQuote($quote);
                    return ['success' => true, 'quote' => $quote];
                } catch (Exception $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            } else {
                $quote = $this->quoteFactory->create();
                return ['success' => true, 'quote' => $quote];
            }
        }
    }

    /**
     * addtocart
     *
     * @param string $params params
     *
     * @return array
     **/
    public function addtocart($params)
    {

        $cartObj = $this->_getCartmobi($params);
        if (isset($cartObj['success']) && $cartObj['success']) {
            $cartObj = $cartObj['cart'];
        } else {
            return ['success' => false, 'message' => 'Can not add to cart.'];
        }
        if (isset($params['super_attribute'])) {
            $params['super_attribute'] = json_decode($params['super_attribute'], true);
        }
        if (isset($params['bundle_option'])) {
            $params['bundle_option'] = json_decode($params['bundle_option'], true);
        }
        if (isset($params['bundle_option_qty'])) {
            $params['bundle_option_qty'] = json_decode($params['bundle_option_qty'], true);
        }
        if (isset($params['selected_configurable_option'])) {
            $params['selected_configurable_option'] = json_decode($params['selected_configurable_option'], true);
        }
        if (isset($params['super_group'])) {
            $params['super_group'] = json_decode($params['super_group'], true);
        }
        if (isset($params['links'])) {
            $params['links'] = json_decode($params['links'], true);
        }
        if (isset($params['Custom'])) {
            $custom_options = json_decode($params['Custom'], true);
            $params['options'] = isset($custom_options['options']) ? $custom_options['options'] : [];
        }
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->resolver->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            } else {
                return ['success' => false, 'message' => 'Quantity can not be null.'];
            }
            $productId = '';
            $qty = '';
            if (isset($params['product_id']) && $params['product_id'] != "" && isset($params['qty']) && $params['qty'] != "") {
                $productId = (int)$params['product_id'];
                $qty = $params['qty'];
            } else {
                $productId = (int)isset($params['product_id']) ? $params['product_id'] : 0;
            }

            $product = $this->_initProduct($productId);


            $related = isset($params['related_product']) ? $params['related_product'] : [];

            /**
             * Check product availability
             */
            if (!$product) {
                return ['success' => false, 'message' => 'We can\'t find product'];
            }

            $cartObj->addProduct($product, $params);

            if (!empty($related)) {
                $cartObj->addProductsByIds(explode(',', $related));
            }
            $storeId = $this->storeManager->getStore();
            $cartObj->getQuote()->setStore($storeId);
            if (isset($params['customer_id']) && $params['customer_id']) {
                $customer = $this->customerRepo->getById($params['customer_id']);
                if ($customer) {
                    $cartObj->getQuote()->assignCustomer($customer);
                }
            }

            $related = isset($params['related_product']) ? $params['related_product'] : [];
            if (!empty($related)) {
                $cartObj->addProductsByIds(explode(',', $related));
            }
            $country = 'US';
            $postcode = '';
            $city = '';
            $regionId = '';
            $region = '';
            $cartObj->save();
            if ($cartObj && $cartObj->getEntityId()) {
                $cartObj->getQuote()->getShippingAddress()->setCountryId($country)->setCity($city)->setPostcode($postcode)->setRegionId($regionId)->setRegion($region)->setCollectShippingRates(true);
            }

            if (isset($params['customer_id']) && $params['customer_id']) {
                $Wishlist = $this->wishlistFactory->create()->loadByCustomerId($params['customer_id']);
                $wishlists = $Wishlist->getItemCollection()->load();
                foreach ($wishlists as $wishlistItem) {
                    if ($wishlistItem->getProductId() == $productId) {
                        $wishlistItem->delete();
                    }
                }
            }

            if ($cartObj && (!$cartObj->getQuote()->getHasError())) {
                /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
                $quoteIdMask = $this->idMaskFactory->create()->load($cartObj->getQuote()->getEntityId(), 'quote_id');
                if ($quoteIdMask->getMaskedId() === null) {
                    $quoteIdMask->setQuoteId($cartObj->getQuote()->getEntityId())->save();
                }
                $mask_id = $cartObj->getQuote()->getEntityId();
                if ($quoteIdMask && $quoteIdMask->getMaskedId())
                    $mask_id = $quoteIdMask->getMaskedId();
                $message = ['cart_id' => ['success' => true, 'cart_id' => $cartObj->getQuote()->getEntityId(), 'mask_id' => $mask_id, 'message' => $product->getName() . ' has been added to your cart.', 'items_count' => (int)$this->cart->getQuote()->getItemsQty()]];
                return $message;
            } else {
                $errorMessages = '';
                foreach ($cartObj->getQuote()->getMessages() as $key => $value) {
                    $errorMessages = $errorMessages . $value->getText();
                }
                return ['cart_id' => ['success' => false, 'message' => $errorMessages]];
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->checkoutSession->getUseNotice(true)) {
                return ['cart_id' => ['success' => false, 'message' => $e->getMessage()]];
            } else {
                $errorMessages = '';
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $errorMessages = $errorMessages . $message;
                }
            }
            return ['cart_id' => ['success' => false, 'message' => $errorMessages]];
        } catch (\Exception $e) {
            return ['cart_id' => ['success' => false, 'message' => 'We can\'t add this item to your shopping cart right now.' . $e->getMessage()]];
        }

    }

    /**
     * _initProduct
     *
     * @param string $productId productId
     *
     * @return string
     */
    public function _initProduct($productId)
    {

        if ($productId) {
            $storeId = $this->storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return $e->getMessage();
            }
        }
        return null;
    }

    /**
     * viewcart
     *
     * @param array $data data
     *
     * @return array
     */
    function viewcart($data)
    {

        $quote = $this->_getQuotemobi($data);
        if (isset($quote['success']) && $quote['success']) {
            $quote = $quote['quote'];
        } else {
            return ['success' => false, 'message' => 'Can load cart.'];
        }
        $priceHelper = $this->pricingHelper;
        $response = [];

        if ($quote !== false && $quote->getItemsCount() != 0) {
            $total = 0;
            $total = $priceHelper->currency($quote->getSubtotal(), true, false);

            $response['data']['grandtotal'] = $priceHelper->currency($quote->getGrandTotal(), true, false);

            $shipping_amount_value = 0;
            $shipping_amount_value = $priceHelper->currency($quote->getShippingAddress()->getShippingInclTax(), true, false);


            $response['data']['total'][] = ['amounttopay' => $total, 'shipping_amount' => $shipping_amount_value, 'tax_amount' => $priceHelper->currency($quote->getShippingAddress()->getTaxAmount(), true, false), 'discount_amount' => $priceHelper->currency($quote->getShippingAddress()->getDiscountAmount(), true, false)];

            if ($quote->getCouponCode() && $quote->getCouponCode() != "") {
                $response['data']['is_discount'] = true;
                $response['data']['coupon'] = $quote->getCouponCode();
            } else
                $response['data']['is_discount'] = false;
            $response['data']['items_count'] = (int)$quote->getItemsQty();
            $response['data']['items_qty'] = (int)$quote->getItemsQty();
            $baseCurrency = $this->directoryHelper->getBaseCurrencyCode();

            try {
                $currencySymbol = $this->currency->getSymbol();

            } catch (\Zend_Currency_Exception $e) {
                $currencySymbol = $this->localeCurrency->getCurrency($baseCurrency)
                    ->getSymbol();
            }

            $response['data']['currency_symbol'] = $currencySymbol;
            $response['data']['currency_code'] = $baseCurrency;
            $items = $quote->getAllVisibleItems();

            /**
             * rewardsystem code starts
             */
            $scopeconfig = $this->scopeConfig;
            $sameProductPoint = $scopeconfig->getValue('reward/setting/product_point');
            $maxOrderPoint = $scopeconfig->getValue('reward/setting/max_point');
            $maxOrderPointEnable = $scopeconfig->getValue('reward/setting/max_point_enable');
            $minicartpoint = 0;
            $results = [];
            $sku = [];
            $rewardDiscount = 0;
            /**
             * rewardsystem code ends
             */
            foreach ($items as $item) {
                /**
                 * rewardsystem code starts
                 */

                if (in_array($item->getSku(), $sku)) {
                    continue;
                }
                $sku[] = $item->getSku();
                $point = $this->productFactory->create()->load($item->getProductId())->getCedRpoint();


                if ($point) {
                    $minicartpoint = $minicartpoint + ($point * $item->getQty());
                } else {
                    if ($sameProductPoint) {
                        $minicartpoint = $minicartpoint + ($sameProductPoint * $item->getQty());

                    } else {
                        $minicartpoint = $minicartpoint;
                    }
                }

                $rewardDiscount = $rewardDiscount + $item->getRewardsystemDiscount();


                /**
                 * rewardsystem code ends
                 */
                $productattrarray = [];

                $item_sub_total = $priceHelper->currency($item->getRowTotalInclTax() - $item->getDiscountAmount(), true, false);

                $imageUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/';
                $smallImageUrl = $imageUrl . $item->getProduct()->getSmallImage();
                $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                if (isset($productOptions['attributes_info']) && count($productOptions['attributes_info']) != 0)
                    $productattrarray['options_selected'] = $productOptions['attributes_info'];
                if (isset($productOptions['bundle_options']) && count($productOptions['bundle_options']) != 0)
                    $productattrarray['bundle_options'] = $productOptions['bundle_options'];
                if (isset($productattrarray['bundle_options']) && count($productattrarray['bundle_options']) != 0) {

                    foreach ($productattrarray['bundle_options'] as $key1 => $value) {
                        if (isset($value['value'])) {
                            foreach ($value['value'] as $key2 => $val) {
                                if (isset($val['price'])) {
                                    $productattrarray['bundle_options'][$key1]['value'][$key2]['price'] = $priceHelper->currency($val['price'], true, false);
                                }
                            }
                        }
                    }
                }
                $productarray = [
                    'product_id' => $item->getProductId(),
                    'item_id' => $item->getId(),
                    'product-name' => $item->getName(),
                    'product_image' => $smallImageUrl,
                    'quantity' => $item->getQty(),
                    'sub-total' => $item_sub_total,
                    'product_type' => $item->getProduct()->getTypeId(),
                    'options_selected' => isset($productattrarray['options_selected']) ? $productattrarray['options_selected'] : '',
                    'bundle_options' => isset($productattrarray['bundle_options']) ? $productattrarray['bundle_options'] : []
                ];
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $isGuestCheckoutEnabled = $this->scopeConfig->getValue('checkout/options/guest_checkout', $storeScope);
                $response['data']['allowed_guest_checkout'] = $isGuestCheckoutEnabled;
                $response['data']['products'][] = $productarray;

            }

            /**
             * rewardsystem code starts
             */
            $rule = $this->ruleFactory->create()->getCollection();
            $rule->setOrder('sort_order', 'ASC')->addFieldToFilter('is_active', '1');
            $condition = $rule->getData();

            $result = 0;

            if (!empty($condition)) {
                foreach ($condition as $key => $value) {

                    $maincondition = $value['simple_condition'];
                    $mainarray = json_decode($maincondition, true);

                    if ($value['stop_rules_processing'] == '1') {

                        $arraytraverse = $this->rewardsystemHelper->traverseArray($mainarray, $productarray);

                        if ($arraytraverse == '1') {
                            if ($value['check_point_in'] == 1) {
                                $base_subtotal = $quote->getSubtotal();
                                $perPoint = floor($base_subtotal * ($value['point_x'] / 100));
                                $result += $perPoint + $minicartpoint;
                                $results['summaryPoint'] = $result;

                            } else {
                                $result += $value['point_x'] + $minicartpoint;
                                $results['summaryPoint'] = $result;

                            }


                        } else {
                            $results['summaryPoint'] = $minicartpoint;

                        }
                        break;
                    }

                    $arraytraverse = $this->rewardsystemHelper->traverseArray($mainarray, $product);

                    if ($arraytraverse == '1') {
                        if ($value['check_point_in'] == 1) {
                            $base_subtotal = $quote->getSubtotal();
                            $perPoint = floor($base_subtotal * ($value['point_x'] / 100));
                            $result = $result + $perPoint;
                            $results['summaryPoint'] = $result;

                        } else {
                            $result = $result + $value['point_x'];
                            $results['summaryPoint'] = $result;
                        }


                    } else {
                        $results['summaryPoint'] = $result;

                    }
                }
                $results['summaryPoint'] = $results['summaryPoint'] + $minicartpoint;
                if ($maxOrderPoint < $minicartpoint && $maxOrderPointEnable == 1) {
                    $results['summaryPoint'] = $maxOrderPoint;
                }
            } else {
                $results['summaryPoint'] = $minicartpoint;
            }
            if ($rewardDiscount) {
                $response['data']['rewardDiscount'] = $currencySymbol . $rewardDiscount;
            }

            $response['data']['points'] = $results['summaryPoint'];

            /*if (isset($data['customer_id'])) {
                $totalpoints = $this->rewardFactory->create()->getTotal($data['customer_id']);
                $response['data']['total_points'] = $totalpoints;
            }*/

            return $response;
        } else {
            return ['success' => false, 'message' => 'Your Shopping Cart Is Empty.'];
        }
    }

    /**
     * Delete item from cart
     *
     * @param array $data data
     *
     * @return
     **/
    function deletecart($data)
    {
        $id = (int)isset($data['item_id']) ? $data['item_id'] : 0;
        if ($id) {
            try {
                $cartObj = $this->_getCartmobi($data);
                if (isset($cartObj['success']) && $cartObj['success']) {
                    $cartObj = $cartObj['cart'];
                } else {
                    return ['success' => false, 'message' => 'Can not add to cart.'];
                }
                $cartObj->removeItem($id)->save();
                $jsonData = ['success' => 'removed_successfully', 'items_count' => (int)$cartObj->getQuote()->getItemsQty()];
                return $jsonData;
            } catch (Exception $e) {
                $jsonData = 'Cannot remove the item.' . $e->getMessage();
                $jsonData = ['success' => 'false', 'message' => $jsonData];
                return $jsonData;
            }
        } else {
            $jsonData = 'Cannot remove the item.';
            $jsonData = ['success' => 'false', 'message' => $jsonData];
            return $jsonData;
        }
    }

    /**
     * Initialize coupon
     *
     * @param array $data data
     *
     * @return array
     */
    public function applycoupon($data)
    {
        $quote = $this->_getQuotemobi($data);
        if (isset($quote['success']) && $quote['success']) {
            $quote = $quote['quote'];
        } else {
            return ['success' => false, 'message' => 'Can load cart.'];
        }
        if (!$quote->getItemsCount()) {
            $jsonData = ['cart_id' => ['success' => false, 'message' => 'Shopping cart is Empty']];
            return $jsonData;
        }

        $couponCode = (string)isset($data['coupon_code']) ? $data['coupon_code'] : '';
        if (isset($data['remove']) && $data['remove'] == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $quote->getCouponCode();
        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $jsonData = ['cart_id' => ['success' => false, 'message' => 'Coupon code is empty.']];
            return $jsonData;
        }

        try {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setCouponCode($couponCode)->collectTotals()->save();
            if ($couponCode) {
                if ($couponCode == $quote->getCouponCode()) {
                    $jsonData = ['cart_id' => ['success' => true, 'message' => 'Coupon code ' . strip_tags($couponCode) . ' was applied.']];
                    return $jsonData;
                } else {
                    $jsonData = ['cart_id' => ['success' => false, 'message' => 'Coupon code ' . strip_tags($couponCode) . ' is not valid.']];
                    return $jsonData;
                }
            } else {
                $jsonData = ['cart_id' => ['success' => true, 'message' => 'Coupon code was canceled.']];
                return $jsonData;
            }

        } catch (Exception $e) {
            $jsonData = ['cart_id' => ['success' => false, 'message' => 'Can\'t apply the coupon code.']];
            return $jsonData;
        }
    }

    /**
     * savebillingshiping
     *
     * @param array $data data
     *
     * @return array
     */
    function savebillingshiping($data)
    {
        $jsonData = [];
        $quote = $this->_getQuotemobi($data);
        if (isset($quote['success']) && $quote['success']) {
            $quote = $quote['quote'];
        } else {
            return ['success' => false, 'message' => 'Can load cart.'];
        }

        if (!is_object($quote)) {
            $jsonData = ['success' => false, 'message' => 'cart have error in address.'];
            return $jsonData;
        }
        $_custom_address = [];
        $customerAddressId = isset($data['address_id']) ? $data['address_id'] : '0';
        $customer_id = isset($data['customer_id']) ? $data['customer_id'] : '0';
        $_custom_address_model = '';
        if ($customerAddressId) {
            $_custom_address_model = $this->addressRepository->retrieve($customerAddressId);
        }
        if (isset($data['billingaddress'])) {
            $BilllingParams = isset($data['billingaddress']) ? $data['billingaddress'] : ' {} ';
            $_custom_address = json_decode($BilllingParams, true);
        }

        $newAddressId = '';
        $customAddress = '';

        if ($customer_id && !$customerAddressId) {
            $_custom_address_model = $this->addressFactory->create();
            $_custom_address_model = $_custom_address_model->create();
            $_custom_address_model->setCustomerId($customer_id);
            $_custom_address_model->setData($_custom_address)->setCustomerId($customer_id)->setisDefaultBilling('1')->setisDefaultShipping('1');
            try {
                $_custom_address_model->save();
                $newAddressId == 0;
                if (is_object($_custom_address_model))
                    $newAddressId = $_custom_address_model->getId();
            } catch (\Magento\Framework\Validator\Exception $e) {
                $jsonData = ['success' => false, 'message' => 'Can\'t save Customer address.' . $e->getMessage()];
                return $jsonData;
            }
        }
        if ($customer_id) {

            if ($customerAddressId) {
                $customerAddressModel = $this->addressRepository->retrieve($customerAddressId);

                $quote->getShippingAddress()->addData($customerAddressModel->getData());
                $quote->getShippingAddress()->save();
                $quote->getBillingAddress()->setSameAsBilling(1);
                $quote->getBillingAddress()->addData($customerAddressModel->getData());
                $quote->getBillingAddress()->save();
                $quote->collectTotals()->save();

                $addressValidation = $quote->getBillingAddress()->validate();
                if ($addressValidation !== true) {
                    $jsonData = ['success' => false, 'message' => 'Please check billing address information. %s' . implode(' ', $addressValidation)];
                }
                if (!$quote->isVirtual()) {
                    $address = $quote->getShippingAddress();
                    $addressValidation = $address->validate();
                    if ($addressValidation !== true) {
                        $jsonData = ['success' => false, 'message' => 'Please check shipping address information. %s' . implode(' ', $addressValidation)];
                    }
                }
                if (!count($jsonData)) {
                    $jsonData = ['success' => true];
                }
            } else {
                $quote->getBillingAddress()->addData($_custom_address_model->getData());
                $quote->getBillingAddress()->save();
                $quote->getBillingAddress()->setSameAsBilling(1);
                $quote->getShippingAddress()->addData($_custom_address_model->getData());
                $quote->getShippingAddress()->save();
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->getShippingAddress()->collectShippingRates();
                $quote->collectTotals()->save();
                $addressValidation = $quote->getBillingAddress()->validate();
                if ($addressValidation !== true) {
                    $jsonData = ['success' => false, 'message' => 'Please check billing address information. %s', implode(' ', $addressValidation)];
                }
                if (!$quote->isVirtual()) {
                    $address = $quote->getShippingAddress();
                    $addressValidation = $address->validate();
                    if ($addressValidation !== true) {
                        $jsonData = ['success' => false, 'message' => 'Please check shipping address information. %s' . implode(' ', $addressValidation)];
                    }
                }
                if (!count($jsonData)) {
                    $jsonData = ['success' => true, 'address_id' => $newAddressId];
                }
            }
        } else {
            if (isset($data['email']) && $data['email'] != '') {
                if ($quote->getCustomerEmail() == null) {
                    $quote->setCustomerId(null)->setCustomerEmail($data['email'])->setCustomerIsGuest(true)->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
                }
            } else {
                if (!count($jsonData)) {
                    $jsonData = ['success' => false, 'message' => 'Email Required for Guest User.'];
                }
            }

            $quote->getBillingAddress()->addData($_custom_address);
            $quote->getBillingAddress()->save();
            $quote->getBillingAddress()->setSameAsBilling(1);
            $quote->getShippingAddress()->addData($_custom_address);
            $quote->getShippingAddress()->save();
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->getShippingAddress()->collectShippingRates();
            $quote->collectTotals()->save();
            $addressValidation = $quote->getBillingAddress()->validate();
            if ($addressValidation !== true) {
                $jsonData = ['success' => false, 'message' => 'Please check billing address information. ' . implode(' ', $addressValidation)];
            }
            if (!$quote->isVirtual()) {
                $address = $quote->getShippingAddress();
                $addressValidation = $address->validate();
                if ($addressValidation !== true) {
                    $jsonData = ['success' => false, 'message' => 'Please check shipping address information.' . implode(' ', $addressValidation)];
                }
            }
            if (!count($jsonData)) {
                $jsonData = ['success' => true];
            }
        }
        return $jsonData;
    }

    /**
     * getsaveshippingpayament
     *
     * @param string $data data
     *
     * @return array
     */
    public function getsaveshippingpayament($data)
    {
        $quote = $this->_getQuotemobi($data);
        if (isset($quote['success']) && $quote['success']) {
            $quote = $quote['quote'];
        } else {
            return ['success' => false, 'message' => 'Can load cart.'];
        }
        $jsonData = [];
        if (!is_object($quote)) {
            $jsonData = ['success' => false, 'message' => 'cart have some error .'];
            return $jsonData;
        }
        // for payments
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $methods = [];
        $paymethods = $this->paymentHelper->getPaymentMethodList(true, true, true);
        $additional_data = [];
        if (is_array($paymethods) && count($paymethods) && isset($paymethods['offline']) && isset($paymethods['offline']['value']) && count($paymethods['offline']['value'])) {
            foreach ($paymethods['offline']['value'] as $code => $name) {
                $isPaymentEnabled = false;
                if (isset($name['value']) && ($name['value'] == 'apppayment'))
                    continue;
                if (isset($name['value']))
                    $isPaymentEnabled = $this->scopeConfig->getValue('payment/' . $name['value'] . '/active', $storeScope);
                if ($isPaymentEnabled) {
                    $methods["methods"][$code] = $name;
                    switch ($name['value']) {
                        case 'banktransfer':
                            $methods["methods"][$code]['additional_data'] = $this->scopeConfig->getValue('payment/' . $name['value'] . '/instructions', $storeScope);
                            break;
                        case 'cashondelivery':
                            $methods["methods"][$code]['additional_data'] = $this->scopeConfig->getValue('payment/' . $name['value'] . '/instructions', $storeScope);
                            break;
                        case 'purchaseorder':
                            $methods["methods"][$code]['additional_data'][] = array(
                                'label' => __('Purchase Order Number'),
                                'type' => 'text',
                                'name' => 'po_number'
                            );
                            break;
                        case 'checkmo':
                            $methods["methods"][$code]['additional_data']['Make Check payable to'] = $this->scopeConfig->getValue('payment/' . $name['value'] . '/payable_to', $storeScope);
                            $methods["methods"][$code]['additional_data']['Send Check to'] = $this->scopeConfig->getValue('payment/' . $name['value'] . '/mailing_address', $storeScope);
                            break;
                    }
                }
            }
        }
        if ($quote && ($quote->getGrandTotal()))
            unset($methods["methods"]['free']);
        // for shipping methods
        $priceHelper = $this->pricingHelper;
        $address = $quote->getShippingAddress();
        $address->collectShippingRates()->save();
        $shippingMethods = [];
        if ($quote->getAddressId()) {
            $shippingMethods = $this->shippingManagement->estimateByAddressId($quote->getEntityId(), $quote->getAddressId());
        } else {
            $shippingMethods = $this->shippingManagement->estimateByExtendedAddress($quote->getEntityId(), $address);
        }

        $newrateCodes = [];
        foreach ($shippingMethods as $rate) {
            $price = 0;
            if ($rate->getAvailable()) {
                $code = $rate->getCarrierCode() . '_' . $rate->getMethodCode();

                $price = $rate->getAmount();
                $price = $priceHelper->currency($price, true, false);
                if ($code != "" && $rate->getMethodTitle() != "") {
                    $newrateCodes["methods"][] = [
                        'label' => $rate->getMethodTitle() . "(" . $price . ")",
                        'value' => $code
                    ];
                }
            }
        }
        if (count($newrateCodes) == 0) {
            $newrateCodes = "No Quotes Availabile.";
        }
        if (count($methods) == 0) {
            $methods = "No Payment Method Availabile.";
        }
        $jsonData = ['success' => true,
            'payments' => $methods,
            'shipping' => $newrateCodes];
        return $jsonData;
    }

    /**
     * saveshippingpayament
     *
     * @param string $data data
     *
     * @return array
     */
    public function saveshippingpayament($data)
    {
        $payment_menthod = isset($data['payment_method']) ? $data['payment_method'] : '';
        $shipping_menthod = isset($data['shipping_method']) ? $data['shipping_method'] : '';
        $is_down = isset($data['is_down']) ? $data['is_down'] : '';
        $quote = $this->_getQuotemobi($data);
        if (isset($quote['success']) && $quote['success']) {
            $quote = $quote['quote'];
        } else {
            return ['success' => false, 'message' => 'Can load cart.'];
        }
        if (!is_object($quote)) {
            $jsonData = ['success' => false, 'message' => 'cart have some error .'];
            return $jsonData;
        }
        $paymentSet = false;
        $shippingSet = false;
        if (strlen($payment_menthod)) {
            try {
                if (isset($data['payment_method']))
                    $data['method'] = $data['payment_method'];
                if (isset($data['cart_id']))
                    $data['quote_id'] = $data['cart_id'];
                if (isset($data['additional_information']))
                    $data['additional_information'] = array($data['additional_information']);
                $quote->getPayment()->setData($data);
                $quote->getPayment()->setMethod($payment_menthod)->save();
                $quote->collectTotals()->save();
                if (!$quote->getHasError()) {
                    $paymentSet = true;
                } else {
                    $errorMessages = '';
                    foreach ($quote->getMessages() as $key => $value) {
                        $errorMessages = $errorMessages . $value->getText();
                    }
                    $jsonData = ['success' => false, 'message' => $errorMessages];
                    return $jsonData;
                }
                if (!($quote->getPayment()->getMethod())) {
                    $jsonData = ['success' => false, 'message' => 'Please select a valid payment method.'];
                    return $jsonData;
                }
            } catch (Exception $e) {
                $jsonData = ['success' => false, 'message' => $e->getMessage()];
                return $jsonData;
            }
        }
        if (strlen($shipping_menthod)) {

            try {
                $shippingAddress = $quote->getShippingAddress();
                $shippingAddress->setShippingMethod($shipping_menthod);
                $shippingAddress->setCollectShippingRates(true);
                $shippingAddress->collectShippingRates();
                $quote->collectTotals()->save();
                if (!$quote->getHasError()) {
                    $shippingSet = true;
                } else {
                    $errorMessages = '';
                    foreach ($quote->getMessages() as $key => $value) {
                        $errorMessages = $errorMessages . $value->getText();
                    }
                    $jsonData = ['success' => false, 'message' => $errorMessages];
                    return $jsonData;
                }
                $address = $quote->getShippingAddress();
                $method = $address->getShippingMethod();
                $rate = $address->getShippingRateByCode($method);
                if (!$quote->isVirtual() && (!$method || !$rate)) {
                    $jsonData = ['success' => false, 'message' => 'Please specif y a shipping method.'];
                    return $jsonData;
                }
            } catch (Exception $e) {
                $jsonData = ['success' => false, 'message' => $e->getMessage()];
                return $jsonData;
            }
        }
        if ($paymentSet && $shippingSet)
            return ['success' => true];
        $jsonData = ['success' => false, 'message' => 'select valid payment or shipping.'];
        return $jsonData;

    }

    /**
     *Save order of current customer
     *
     * @param Postdata $data data
     *
     * @return array
     **/
    public function saveorder($data)
    {
        $quote = $this->_getQuotemobi($data);
        if (isset($quote['success']) && $quote['success']) {
            $quote = $quote['quote'];
        } else {
            return ['success' => false, 'message' => 'Can load cart.'];
        }
        if (!is_object($quote)) {
            $jsonData = ['success' => false, 'message' => 'cart have some error .'];
            return $jsonData;
        }
        try {

            //check if  quote have item
            if ($quote->getItemsCount()) {
                $quote->collectTotals();

                $quote->save();
                $quoteid = $quote->getId();
                $discountAmount = 0;
                foreach ($quote->getAllItems() as $item) {
                    if($item->getParentItemId()) {
                        continue;
                    }
                    $discountAmount = $discountAmount + $item->getRewardsystemDiscount();
                }
                if ($discountAmount) {
                    if ($quoteid) {

                        $quote->setSubtotal(0);
                        $quote->setBaseSubtotal(0);
                        $quote->setSubtotalWithDiscount(0);
                        $quote->setBaseSubtotalWithDiscount(0);
                        $quote->setGrandTotal(0);
                        $quote->setBaseGrandTotal(0);
                        $itemAllowed = $quote->isVirtual() ? ('billing') : ('shipping');
                        $count = count($quote->getAllAddresses());
                        $valid = true;
                        foreach ($quote->getAllAddresses() as $address) {

                            $quote->setSubtotal((float)$quote->getSubtotal() + $address->getSubtotal());
                            $quote->setBaseSubtotal((float)$quote->getBaseSubtotal() + $address->getBaseSubtotal());
                            $quote->setGrandTotal((float)$quote->getGrandTotal() + $address->getGrandTotal());
                            $quote->setBaseGrandTotal((float)$quote->getBaseGrandTotal() + $address->getBaseGrandTotal());
                            $quote->save();

                            if ($quote->getBaseSubtotal() >= $discountAmount) {

                                $quote->setGrandTotal($quote->getBaseSubtotal() + $quote->getShippingAddress()->getTaxAmount() + $quote->getShippingAddress()->getShippingInclTax() - $discountAmount)
                                    ->setBaseGrandTotal($quote->getBaseSubtotal() + $quote->getShippingAddress()->getTaxAmount() + $quote->getShippingAddress()->getShippingInclTax() - $discountAmount)
                                    ->setSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount)
                                    ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal() - $discountAmount);
                                $quote->save();

                            }

                            if ($address->getAddressType() == $itemAllowed) {

                                $address->setTotalAmount('fee', -$discountAmount);
                                $address->setBaseTotalAmount('fee', -$discountAmount);
                                $address->setRewardsystemDiscount($discountAmount);
                                $address->setRewardsystemBaseAmount($discountAmount);
                                $address->setSubtotalWithDiscount((float)$address->getSubtotalWithDiscount() - $discountAmount);
                                $address->setGrandTotal($address->getSubtotalWithDiscount() + $address->getShippingAmount() + $address->getTaxAmount() - $discountAmount);
                                $address->setBaseSubtotalWithDiscount((float)$address->getBaseSubtotalWithDiscount() - $discountAmount);
                                $address->setBaseGrandTotal($address->getSubtotalWithDiscount() + $address->getShippingAmount() + $address->getTaxAmount() - $discountAmount);

                                $address->setRewardsystemDiscount(-($address->getRewardsystemDiscount() - $discountAmount));
                                $address->setDiscountDescription($address->getDiscountDescription() . ', Custom Discount');
                                $address->setRewardsystemBaseAmount(-($address->getRewardsystemBaseAmount() - $discountAmount));

                                $address->save();
                            }//end: if


                        } //end: foreach

                        $c = count($quote->getAllItems());
                        foreach ($quote->getAllItems() as $item) {
                            if($item->getParentItemId()) {
                                $c = $c - 1;
                            }
                        }

                        $discountAmount = $discountAmount / $c;

                        foreach ($quote->getAllItems() as $item) {
                            $item->setBaseOriginalPrice($item->getPrice() - $discountAmount);
                            $item->setOriginalPrice($item->getPrice() - $discountAmount);
                            $item->setDiscountAmount($discountAmount);
                            $item->setBaseDiscountAmount($discountAmount);
                            $item->setRewardsystemDiscount($discountAmount);
                            $item->setRewardsystemBaseAmount($discountAmount);

                            $item->save();
                        }
                        $quote->save();

                    }
                }

                $quoteManagement = $this->quoteManagementFactory->create();
                $order = $quoteManagement->submit($quote);

                if ($discountAmount) {
                    $this->UpdateRewardPoints($order, $discountAmount);
                }

                $quote->setIsActive(false)->save();
                if (is_object($order) && $order->getId()) {

                    $order->setEmailSent(1);
                    try {
                        $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
                        if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
                            try {
                                $this->orderSender->send($order);
                            } catch (\Exception $e) {

                            }
                        }
                    } catch (Exception $e) {
                        return ['success' => false, 'message' => $e->getMessage()];
                    }
                    return $jsonData = ['success' => true, 'order_id' => $order->getIncrementId(), 'grandtotal' => $order->getGrandTotal(), 'currency_code' => $order->getOrderCurrencyCode()];
                }
            } else {
                return $jsonData = ['success' => false, 'message' => 'Quote is empty.'];
            }
        } catch (Exception $e) {
            return $jsonData = ['success' => false, 'message' => $e->getMessage()];
        }
    }


    /**
     * @param $order
     * @param $discountAmount
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function UpdateRewardPoints($order, $discountAmount)
    {
        try {
            $usedpoint = $this->calculate($discountAmount);

            $orderDetail = $this->orderFactory->create()->load($order->getId());
            $saveOrderDetail = $orderDetail->getData();

            $scopeconfig = $this->scopeConfig;
            $sameProductPoint = $scopeconfig->getValue('reward/setting/product_point');
            $maxOrderPoint = $scopeconfig->getValue('reward/setting/max_point');
            $maxOrderPointEnable = $scopeconfig->getValue('reward/setting/max_point_enable');
            $minicartpoint = 0;
            $results = [];
            $sku = [];


            $firstpurchase = $scopeconfig->getValue('reward/setting/first_ref_purchase');
            $anypurchase = $scopeconfig->getValue('reward/setting/ref_purchase');

            if ($usedpoint) {
                if ($saveOrderDetail['customer_id']) {
                    $items = $orderDetail->getAllVisibleItems();

                    foreach ($items as $item) {
                        if (in_array($item->getSku(), $sku)) {
                            continue;
                        }
                        $sku[] = $item->getSku();

                        $point = $this->productFactory->create()->load($item->getProductId())->getCedRpoint();

                        if ($point) {
                            $minicartpoint = $minicartpoint + ($point * $item->getQtyOrdered());
                        } else {
                            if ($sameProductPoint) {
                                $minicartpoint = $minicartpoint + ($sameProductPoint * $item->getQtyOrdered());

                            } else {
                                $minicartpoint = $minicartpoint;
                            }
                        }

                        $rule = $this->ruleFactory->create()->getCollection();
                        $rule->setOrder('sort_order', 'ASC')->addFieldToFilter('is_active', '1');
                        $condition = $rule->getData();

                        if (!empty($condition)) {
                            foreach ($condition as $key => $value) {

                                $maincondition = $value['simple_condition'];
                                $mainarray = unserialize($maincondition);

                                if ($value['stop_rules_processing'] == '1') {

                                    $arraytraverse = $this->rewardsystemHelper->traverseArray($mainarray, $product);

                                    if ($arraytraverse == '1') {
                                        if ($value['check_point_in'] == 1) {
                                            $base_subtotal = $quote->getSubtotal();
                                            $perPoint = floor($base_subtotal * ($value['point_x'] / 100));
                                            $result += $perPoint + $minicartpoint;
                                            $results['summaryPoint'] = $result;

                                        } else {
                                            $result += $value['point_x'] + $minicartpoint;
                                            $results['summaryPoint'] = $result;

                                        }


                                    } else {
                                        $results['summaryPoint'] = $minicartpoint;

                                    }
                                    break;
                                }

                                $arraytraverse = $this->rewardsystemHelper->traverseArray($mainarray, $product);

                                if ($arraytraverse == '1') {
                                    if ($value['check_point_in'] == 1) {
                                        $base_subtotal = $quote->getSubtotal();
                                        $perPoint = floor($base_subtotal * ($value['point_x'] / 100));
                                        $result = $result + $perPoint;
                                        $results['summaryPoint'] = $result;

                                    } else {
                                        try {

                                            $result = $result + $value['point_x'];
                                            $results['summaryPoint'] = $result;

                                        } catch (\Exception $e) {
                                            throw new \Magento\Framework\Exception\LocalizedException (__($e->getMessage()));
                                        }

                                    }


                                } else {
                                    $results['summaryPoint'] = $result;

                                }
                            }
                            $results['summaryPoint'] = $results['summaryPoint'] + $minicartpoint;
                            if ($maxOrderPoint < $minicartpoint && $maxOrderPointEnable == 1) {
                                $results['summaryPoint'] = $maxOrderPoint;
                            }
                        } else {
                            $results['summaryPoint'] = $minicartpoint;
                        }

                    }

                    if ($results['summaryPoint']) {

                        $model = $this->regisuserpointFactory->create();
                        $model->setCustomerId($saveOrderDetail['customer_id']);
                        $model->setPoint($results['summaryPoint']);
                        $model->setTitle('Receive Rewardpoint for Ordered successfully');
                        $model->setCreatingDate($saveOrderDetail['created_at']);
                        $model->setStatus($saveOrderDetail['status']);
                        $model->setPointUsed($usedpoint);
                        $model->setOrderId($orderDetail->getId());
                        $model->save();
                    }
                    if ($firstpurchase || $anypurchase) {

                        $_orders = $this->orderFactory->create()->getCollection()->addFieldToFilter('customer_id', $saveOrderDetail['customer_id'])->getData();
                        $parentId = $this->regisuserpointFactory->create()->getCollection()->addFieldToFilter('is_register', 1)->addFieldToFilter('customer_id', $saveOrderDetail['customer_id'])->getFirstItem()->getParentCustomer();
                        if ($parentId) {
                            if (count($_orders) == 1) {

                                $rcmodel = $this->regisuserpointFactory->create();
                                $rcmodel->setTitle('Received Rewardpoint due to referal purchase');
                                $rcmodel->setCreatingDate($saveOrderDetail['created_at']);
                                $rcmodel->setStatus('complete');
                                $rcmodel->setPoint($firstpurchase);
                                $rcmodel->setCustomerId($parentId);
                                $rcmodel->save();

                            } else {
                                $rcmodel = $this->regisuserpointFactory->create();
                                $rcmodel->setTitle('Received Rewardpoint due to referal purchase');
                                $rcmodel->setCreatingDate($saveOrderDetail['created_at']);
                                $rcmodel->setStatus('complete');
                                $rcmodel->setPoint($anypurchase);
                                $rcmodel->setCustomerId($parentId);
                                $rcmodel->save();

                            }
                        }
                    }
                    return true;
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException (__($e->getMessage()));
        }
    }

    /**
     * @param $value
     * @return float|int
     */
    public function calculate($value)
    {
        $originalBasePrice = $this->convertPriceRate($value);
        $pointPriceRate = $this->scopeConfig->getValue('reward/setting/point_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $definePoint = $this->scopeConfig->getValue('reward/setting/point', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $usedpoint = ($definePoint / $pointPriceRate) * $originalBasePrice;
        return $usedpoint;
    }

    /**
     * @param int $amount
     * @param null $store
     * @param null $currency
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function convertPriceRate($amount = 0, $store = null, $currency = null)
    {
        $getCurrentCurrencyRate = $this->storeManager->getStore()->getCurrentCurrencyRate();
        $value = $amount / $getCurrentCurrencyRate;
        return $value;

    }

    /**
     * getorderdata
     *
     * @param string $data data
     *
     * @return array
     */
    function getorderdata($data)
    {
        $increment_id = isset($data['id']) ? $data['id'] : 0;
        if ($increment_id) {
            $order = $this->orderFactory->loadByIncrementId($increment_id);
            return $order->getData();
        } else {
            return ['error' => "Order Id not Found."];
        }

    }

    /**
     * getorderdata
     *
     * @param string $data data
     *
     * @return array
     */
    function getcartcount($data)
    {
        $quote = $this->_getQuotemobi($data);

        $defaultStoreId = $this->storeManager->getStore()->getId();
        $resolver = $this->resolver;
        $locale = $resolver->getLocale();
        if (!$locale)
            $locale = 'en_US';
        $gender = 'guest';
        $name = 'guest';


        if (isset($data['customer_id']) && $data['customer_id']) {
            $customer = $this->customerFactory->create()->load($data['customer_id']);
            switch ($customer->getData('gender')) {
                case '1':
                    $gender = 'male';
                    break;
                case '2':
                    $gender = 'female';
                    break;
                case '3':
                    $gender = 'guest';
                    break;
            }
            $name = $customer->getData('firstname');
        }


        if (isset($quote['success']) && $quote['success']) {
            $quote = $quote['quote'];
        } else {
            $jsonData = ['success' => false, 'default_store' => $defaultStoreId, 'message' => 'no_count', 'locale' => $locale, 'gender' => $gender, 'name' => $name];
            return $jsonData;
        }


        if (!is_object($quote)) {
            $jsonData = ['success' => false, 'message' => 'cart have some error .', 'gender' => $gender, 'locale' => $locale, 'name' => $name];
            return $jsonData;
        }
        if ($quote->getHasError()) {
            $errorMessages = '';
            foreach ($quote->getMessages() as $key => $value) {
                $errorMessages = $errorMessages . $value->getText();
            }
            $jsonData = ['success' => false, 'message' => $errorMessages, 'gender' => $gender, 'locale' => $locale, 'name' => $name];
            return $jsonData;
        }


        if ($quote->getItemsCount() >= 0) {
            $response = ['success' => true, 'default_store' => $defaultStoreId, 'items_count' => (int)$quote->getItemsQty(), 'cart_id' => (int)$quote->getEntityId(), 'gender' => ($gender == '') ? 'guest' : $gender, 'locale' => $locale, 'name' => $name];
            return $response;
        } else {
            $jsonData = ['success' => false, 'default_store' => $defaultStoreId, 'message' => 'no_count', 'gender' => $gender, 'locale' => $locale, 'name' => $name];
            return $jsonData;
        }

    }

    /**
     * getCountryDropdown
     *
     * @return array
     */
    function getCountryDropdown()
    {
        $countryHelper = $this->country;
        $countryList = $countryHelper->toOptionArray(false);
        return ['country_list' => $countryList];
    }

    /**
     * Empty customer's shopping cart
     *
     * @param object $cartObj cart
     * @return void
     */
    protected function _emptyShoppingCart($cartObj)
    {
        try {
            $cartObj->truncate()->save();
            return ['success' => true, 'message' => 'cart empty successfully'];
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception, __('We can\'t update the shopping cart.')];
        }
    }

    /**
     * Empty customer's shopping cart
     *
     * @param object $cartObj cart
     *
     * @param string $params params
     *
     * @return void
     */
    public function _updateCartOptions($cartObj, $params)
    {
        $id = isset($params['item_id']) ? (int)$params['item_id'] : '';
        if (isset($params['super_attribute'])) {
            $params['super_attribute'] = json_decode($params['super_attribute'], true);
        }
        if (isset($params['bundle_option'])) {
            $params['bundle_option'] = json_decode($params['bundle_option'], true);
        }
        if (isset($params['bundle_option_qty'])) {
            $params['bundle_option_qty'] = json_decode($params['bundle_option_qty'], true);
        }
        if (isset($params['selected_configurable_option'])) {
            $params['selected_configurable_option'] = json_decode($params['selected_configurable_option'], true);
        }
        if (isset($params['links'])) {
            $params['links'] = json_decode($params['links'], true);
        }
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(['locale' => $this->resolver->getLocale()]);
                $params['qty'] = $filter->filter($params['qty']);
            }

            $item = $cartObj->getQuote()->getItemById($id);
            if (!$item) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the quote item.'));
            }

            $item = $cartObj->updateItem($id, new \Magento\Framework\DataObject($params));
            if (is_string($item)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item));
            }
            if ($item->getHasError()) {
                return ['success' => false, 'message' => __($item->getMessage())];

            }

            $related = isset($params['related_product']) ? $params['related_product'] : [];
            if (!empty($related)) {
                $cartObj->addProductsByIds(explode(',', $related));
            }

            $cartObj->save();

            if (!$cartObj->getQuote()->getHasError()) {
                $message = __('%1 was updated in your shopping cart.', $this->escaper->escapeHtml($item->getProduct()->getName()));
                return ['success' => true, 'message' => $message];
            } else {
                $emsg = '';
                $errorMessages = $cartObj->getQuote()->getMessages();
                foreach ($errorMessages as $key => $value) {
                    $emsg = $emsg . $value;
                }
                return ['success' => false, 'message' => $emsg];
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $messager = '';
            $messages = array_unique(explode("\n", $e->getMessage()));
            foreach ($messages as $message) {
                $messager = $messager . $message;
            }
            return ['success' => false, 'message' => $messager];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage(), __('We can\'t update the item right now.')];
        }
    }

    /**
     * Empty customer's shopping cart
     *
     * @param object $cartObj cart
     * @param string $params params
     * @return void
     */
    protected function _updateShoppingCart($cartObj, $params)
    {
        if (isset($params['super_attribute'])) {
            $params['super_attribute'] = json_decode($params['super_attribute'], true);
        }
        if (isset($params['bundle_option'])) {
            $params['bundle_option'] = json_decode($params['bundle_option'], true);
        }
        if (isset($params['bundle_option_qty'])) {
            $params['bundle_option_qty'] = json_decode($params['bundle_option_qty'], true);
        }
        if (isset($params['selected_configurable_option'])) {
            $params['selected_configurable_option'] = json_decode($params['selected_configurable_option'], true);
        }
        if (isset($params['links'])) {
            $params['links'] = json_decode($params['links'], true);
        }
        try {
            $item_id = isset($params['item_id']) ? (int)$params['item_id'] : 0;
            if ($item_id) {
                $filter = new \Zend_Filter_LocalizedToNormalized(['locale' => $this->resolver->getLocale()]);
                $cartData[$item_id]['qty'] = $filter->filter(trim($params['qty']));
                $cartObj->suggestItemsQty($cartData);
                $cartObj->updateItems($cartData)->save();
                return ['success' => true, 'message' => 'updated successfully'];
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e, __('We can\'t update the shopping cart.')];
        }
    }

    /**
     * Update shopping cart data action
     *
     * @param string $params params
     *
     * @return array
     */
    public function updateqty($params)
    {

        $cartObj = $this->_getCartmobi($params);
        if (isset($cartObj['success']) && $cartObj['success']) {
            $cartObj = $cartObj['cart'];
        } else {
            return ['success' => false, 'message' => 'Can not updateqty.'];
        }
        $result = $this->_updateShoppingCart($cartObj, $params);
        return $result;
    }

    /**
     * Update shopping cart data action
     *
     * @param string $params params
     *
     * @return array
     */
    public function updatecartoptions($params)
    {
        $cartObj = $this->_getCartmobi($params);
        if (isset($cartObj['success']) && $cartObj['success']) {
            $cartObj = $cartObj['cart'];
        } else {
            return ['success' => false, 'message' => 'Can not update cart.'];
        }
        $result = $this->_updateCartOptions($cartObj, $params);
        return $result;
    }

    /**
     * Update shopping cart data action
     *
     * @param string $params params
     *
     * @return array
     */
    public function emptycart($params)
    {
        $cartObj = $this->_getCartmobi($params);
        if (isset($cartObj['success']) && $cartObj['success']) {
            $cartObj = $cartObj['cart'];
        } else {
            return ['success' => false, 'message' => 'Can not empty cart.'];
        }
        $result = $this->_emptyShoppingCart($cartObj);
        return $result;
    }


    /*for additional order tab */

    /**
     * saveshippingpayament
     *
     * @param string $data data
     *
     * @return array
     */
    public function additionalinfo($data)
    {
        $order_id = isset($data['order_id']) ? $data['order_id'] : '';
        $additional_info = isset($data['additional_information']) ? json_decode($data['additional_information'])->message : '';
        $order = $this->orderFactory->create()->loadByIncrementId($data['order_id']);

        if ($order && $order->getId()) {
            try {
                $quoteId = $order->getQuoteId();
                $quote = $this->quoteFactory->create()->load($quoteId);
                $quote->getPayment()->setAdditionalInformation($additional_info);
                $quote->save();
                if (isset($data['failure']) && ($data['failure'] == 'true')) {

                    $this->orderManagement->cancel($order->getId());

                    return ['success' => false];

                } elseif (isset($data['failure']) && ($data['failure'] == 'false')) {
                    $config_code = $this->scopeConfig->getValue('payment/apppayment/order_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $order->setState($config_code)
                        ->setStatus($this->config->getStateDefaultStatus($config_code))
                        ->save();
                    return ['success' => true, 'message' => 'updated successfully'];
                }

            } catch (Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        } else {
            return ['success' => false, 'message' => 'Order does not exists'];
        }
    }
} 