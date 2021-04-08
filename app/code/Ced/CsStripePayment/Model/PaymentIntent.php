<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CsStripePayment
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsStripePayment\Model;

use Magento\Framework\Validator\Exception;
use Magento\Framework\Exception\LocalizedException;
use StripeIntegration\Payments\Helper\Logger;

class PaymentIntent extends \StripeIntegration\Payments\Model\PaymentIntent
{
    public $paymentIntent = null;
    public $paymentIntentsCache = [];
    public $params = [];
    public $stopUpdatesForThisSession = false;
    public $quote = null; // Overwrites default quote
    public $order = null;
    public $capture = null; // Overwrites default capture method

    const CAPTURED = "succeeded";
    const AUTHORIZED = "requires_capture";
    const CAPTURE_METHOD_MANUAL = "manual";
    const CAPTURE_METHOD_AUTOMATIC = "automatic";
    const REQUIRES_ACTION = "requires_action";

	public function __construct(
	\StripeIntegration\Payments\Helper\SetupIntent $setupIntent,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \StripeIntegration\Payments\Helper\Rollback $rollback,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\CsStripePayment\Model\ManagedFactory $managedFactory,
        \Ced\CsMarketplace\Model\VsettingsFactory $vsettingfactory,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Session\Generic $session,
        \StripeIntegration\Payments\Model\StripeCustomer $customer,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \StripeIntegration\Payments\Helper\Subscriptions $subscriptionsHelper,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Checkout\Helper\Data $checkoutHelper
    ){
        parent::__construct(
            $helper,
            $rollback,
	    $setupIntent,
            $subscriptionsHelper,
            $config,
            $addressFactory,
            $quoteFactory,
            $quoteRepository,
            $session,
            $checkoutHelper,
            $context,
            $registry,
            $resource,
            $resourceCollection
        ); 
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
		$this->_objectManager = $objectInterface;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->vsettingfactory = $vsettingfactory;
        $this->managedFactory = $managedFactory;
        $this->config = $config;
    }
	public function confirmAndAssociateWithOrder($order, $payment)
    {   //die(get_class($this));
        if ($payment->getAdditionalInformation("is_recurring_subscription"))
            return null;

        $vendorsBaseOrder = $this->vendorOrders ($order);
        $vstripeActive = $this->_scopeConfig->getValue ( 'ced_csmarketplace/csstripe/active' );

        if(empty($vendorsBaseOrder) || !$vstripeActive)
        	return parent::confirmAndAssociateWithOrder($order, $payment); 

        $hasSubscriptions = $this->helper->hasSubscriptionsIn($order->getAllItems());

        $quote = $order->getQuote();
        if (empty($quote) || !is_numeric($quote->getGrandTotal()))
            $this->quote = $quote = $this->quoteRepository->get($order->getQuoteId());
        if (empty($quote) || !is_numeric($quote->getGrandTotal()))
            throw new \Exception("Invalid quote used for Payment Intent");

        // Save the quote so that we don't lose the reserved order ID in the case of a payment error
        $quote->save();

        // Create subscriptions if any
        $piSecrets = $this->createSubscriptionsFor($order);

        $created = $this->create($quote, $payment); // Load or create the Payment Intent

        if (!$created && $hasSubscriptions)
        {
            if (count($piSecrets) > 0 && $this->helper->isMultiShipping())
            {
                reset($piSecrets);
                $paymentIntentId = key($piSecrets); // count($piSecrets) should always be 1 here
                return $this->redirectToMultiShippingAuthorizationPage($payment, $paymentIntentId);
            }

            // This makes sure that if another quote observer is triggered, we do not update the PI
            $this->stopUpdatesForThisSession = true;

            // We may be buying a subscription which does not need a Payment Intent created manually
            if ($this->paymentIntent)
            {
                $object = clone $this->paymentIntent;
                $this->destroy($order->getQuoteId());
            }
            else
                $object = null;

            $this->triggerAuthentication($piSecrets, $order, $payment);

            // Let's save the Stripe customer ID on the order's payment in case the customer registers after placing the order
            if (!empty($this->subscriptionData['stripeCustomerId']))
                $payment->setAdditionalInformation("customer_stripe_id", $this->subscriptionData['stripeCustomerId']);

            return $object;
        }

        if (!$this->paymentIntent)
            throw new LocalizedException(__("Unable to create payment intent"));

        if (!$this->isSuccessfulStatus())
        {
            $this->order = $order;
            $save = ($this->helper->isMultiShipping() || $payment->getAdditionalInformation("save_card"));
            $this->setPaymentMethod($payment->getAdditionalInformation("token"), $save, false);
            $params = $this->config->getStripeParamsFrom($order);
            $this->paymentIntent->description = $params['description'];
            $this->paymentIntent->metadata = $params['metadata'];

            if ($this->helper->isMultiShipping())
                $this->paymentIntent->amount = $params['amount'];

            $this->updatePaymentIntent($quote);

            $confirmParams = [];

            if ($this->helper->isAdmin() && $this->config->isMOTOExemptionsEnabled())
                $confirmParams = ["payment_method_options" => ["card" => ["moto" => "true"]]];

            try
            {
                $this->paymentIntent->confirm($confirmParams);
            }
            catch (\Exception $e)
            {
                $this->helper->maskException($e);
            }

            if ($this->requiresAction())
                $piSecrets[] = $this->getClientSecret();

            if (count($piSecrets) > 0 && $this->helper->isMultiShipping())
                return $this->redirectToMultiShippingAuthorizationPage($payment, $this->paymentIntent->id);
        }
        //print_r(json_decode($this->paymentIntent,true));die('=-=-');
        
        $this->triggerAuthentication($piSecrets, $order, $payment);

        $this->processAuthenticatedOrder($order, $this->paymentIntent);

        //vendor wise split & transfer payment code start

        $account_type = $this->_scopeConfig->getValue ( 'ced_csmarketplace/csstripe/account_type' );
        $baseToGlobalRate = $order->getBaseToGlobalRate () ? $order->getBaseToGlobalRate () : 1;
        //$totalVendorPrice = 0;
        $orderID = $order->getIncrementId ();
        try{
        	foreach($vendorsBaseOrder as $vendorId => $baseOrderTotal )
            {
                
                $shippingprice = $this->getShipping($order->getQuoteId(), $vendorId );
                $shippingprice = $shippingprice ? $shippingprice : 0.00;
                $amount = $baseOrderTotal ['order_total'] + $shippingprice;
                $helper = $this->_objectManager->get ( 'Ced\CsMarketplace\Helper\Acl' )->setStoreId ( $order->getStoreId())->setOrder ( $order )->setVendorId ( $vendorId );
                $commissionSetting = $helper->getCommissionSettings ( $vendorId );
                $commissionSetting ['item_commission'] = $baseOrderTotal ['item_commission'];
                $commission = $helper->calculateCommission($this->_objectManager->create ( 'Magento\Directory\Helper\Data' )->currencyConvert ( $baseOrderTotal ['order_total'], $order->getBaseCurrencyCode (), $order->getOrderCurrencyCode () ), $baseOrderTotal ['order_total'], $baseToGlobalRate, $commissionSetting );
                $amount = $amount - $commission ['fee'];

                try {
                    if($payment->getMethod () == 'stripe_payments') {
                        $vsetting = $this->vsettingfactory->create()->getCollection()->addFieldToFilter('vendor_id',$vendorId)->addFieldToFilter('group','payment')->addFieldToFilter('key','payment/vstripe/active');
                        
                        if(!count($vsetting->getData()))
                        	continue;
                        $stripe_acc = 0;
                        //echo $account_type;die;
                        if ($account_type == 'managed'){
                            $check_acc = $this->managedFactory->create()->load($vendorId,'vendor_id');
                            //print_r($check_acc->getData());die('in');
                            if (is_array($check_acc->getData()) && count($check_acc->getData()))
                                $stripe_acc = $check_acc['account_id'];
                            else
                            	continue;
                        }else{ //die('=-=-');
                            $getData = $this->_objectManager->create('Ced\CsStripePayment\Model\Standalone')->getCollection ()->getData ();
                            /**
                             * get token, if Mode is set to standalone
                             */
                            $stripe_account = 0;
                            foreach ( $getData as $get ) {
                                if ($vendorId == $get ['vendor_id']) {
                                    $stripe_account = $get ['stripe_user_id'];
                                    $token = $get ['access_token'];
                                    break;
                                }
                            }
                            $stripe_acc = $stripe_account;
                        }
                        try {
                            $transfer =   \Stripe\Transfer::create([
                                "amount" => $amount*100, // amount in cents
                                "currency" => $this->params['currency'],
                                "source_transaction" => $this->paymentIntent->charges->data[0]->id,
                                "destination" => $stripe_acc,
                                "transfer_group" => $orderID 
                            ]);
                        }catch ( \Stripe\Exception\CardException $e ) {
                            //echo $stripe_acc.'<br>';
                        	//echo $e->getMessage();die('here in catch');
                            throw new \Magento\Framework\Exception\LocalizedException(__( $e->getMessage()));
                        }
                        //print_r($transfer);die('-=-=-');
                        $transferId = $transfer->id;
                        $status = 2;
                        //$this->_processSuccessResult( $payment, $transferId, $amount);
                        $event_data_array = [
                            'transaction_id' => $transferId,
                            'vendor_id' => $vendorId,
                            'amount' => $amount,
                            'order_id' => $orderID
                        ];
                        $this->saveVpayment($event_data_array, $vendorId, $status );
                    }
                } catch ( \Exception $e ) { 
                    throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                }
                
            }

        }catch(\Exception $e){
        	throw new \Magento\Framework\Exception\LocalizedException ( __ ( $e->getMessage () ) );
        }
        //vendor wise split & transfer payment code end

        // If this method is called, we should also clear the PI from cache because it cannot be reused
        $object = clone $this->paymentIntent;
        $this->destroy($quote->getId());

        // This makes sure that if another quote observer is triggered, we do not update the PI
        $this->stopUpdatesForThisSession = true;

        return $object;
    }

    public function saveVpayment($eventdata, $vendorId, $status) 
    {
        try {
            //$currencyCode = $this->_storeManager->getStore ( null )->getBaseCurrencyCode ();

            $model = $this->_objectManager->create ( '\Ced\CsMarketplace\Model\Vpayment' );

            $transId = $this->_objectManager->create ( '\Ced\CsMarketplace\Model\Vorders' )->load ( $vendorId, 'vendor_id' );
            $vendor_amount = $eventdata ['amount'];
            $data ['transaction_id'] = $eventdata ['transaction_id'];
            $data['order_id'] = $eventdata ['order_id'];
            $data ['transaction_type'] = 0;
            $data ['payment_method'] = 1;
            $data ['vendor_id'] = $eventdata ['vendor_id'];
            $data ['amount_desc'] = '{"' . $eventdata ['order_id'] . '":"' . $eventdata ['amount'] . '"}';
            $data ['base_currency'] = $this->params['currency'];
            $data ['payment_code'] = 'ced_csstripe_method_one';
            $data ['amount'] = $eventdata ['amount'];
            $data ['base_net_amount'] = $eventdata ['amount'];
            $data ['net_amount'] = $eventdata ['amount'];
            $data ['base_amount'] = $eventdata ['amount'];
            $data ['base_fee'] = '0.00';
            $data ['tax'] = 0.00;
            $data ['payment_detail'] = isset ( $data ['payment_detail'] ) ? $data ['payment_detail'] : 'n/a';
            $data ['status'] = $status;
            // $model->setData($data);
            $model->addData ( $data );
            $openStatus = $model->getOpenStatus();
            $model->setStatus ( $openStatus );
            $model->saveOrders ( $data );
            $model->save ();
        } catch ( \Exception $e ) {
            throw new \Magento\Framework\Exception\LocalizedException ( __ ( $e->getMessage () ) );
        }
    }

    public function vendorOrders($order)
    {
        try {
            $products = $order->getAllItems ();
            $vendorsBaseOrder = array ();
            $vendorQty = array ();
            foreach ( $products as $key => $item ) {
                if ($vendor_id = $this->_objectManager->create ( 'Ced\CsMarketplace\Model\Vproducts' )->getVendorIdByProduct ( $item->getProductId () )) {
                    $price = 0;
                    $price = $this->getTotalAmount ( $item );
                    $vendorsBaseOrder [$vendor_id] ['order_total'] = isset ( $vendorsBaseOrder [$vendor_id] ['order_total'] ) ? ($vendorsBaseOrder [$vendor_id] ['order_total'] + $price) : $price;
                    $vendorsBaseOrder [$vendor_id] ['item_commission'] [$item->getQuoteItemId ()] = $price;

                }
            }
        }catch(\Exception $e){
            throw new CouldNotSaveException ( __ ( 'An error occurred on the server. Please try to place the order again.' ) );
        }
        //print_r($vendorsBaseOrder);die('=-=-=--');
        return $vendorsBaseOrder;
    }

    public function getTotalAmount($item) {
        return $item->getRowTotal () + $item->getTaxAmount () + $item->getHiddenTaxAmount () + $item->getWeeeTaxAppliedRowAmount () - $item->getDiscountAmount ();
    }

    public function getShipping($quoteId,$ordervendorId)
    {
        if($this->_objectManager->create('Magento\Framework\Module\Manager')->isEnabled('Ced_CsMultiShipping')) {
            $shippingPrice ='';
            if ($quoteId) {
                $quote = $this->quoteFactory->create()->load($quoteId);
                if ($quote && $quote->getId()) {

                    $addresses = $quote->getAllShippingAddresses();
                    foreach ($addresses as $address) {
                        if ($address) {
                            $shippingMethod = $address->getShippingMethod();
                            if (substr($shippingMethod, 0, 12) == 'vendor_rates') {
                                $shippingMethod = str_replace('vendor_rates_', '', $shippingMethod);
                            }
                            $shippingMethods = explode(':', $shippingMethod);
                            $vendorId = 0;
                            foreach ($shippingMethods as $method) {
                                $rate = $address->getShippingRateByCode($method);
                                $methodInfo = explode('~', $method);
                                if (sizeof($methodInfo)!= 2) {
                                    continue;
                                }
                                $vendorId = isset($methodInfo [1])?$methodInfo[1]:"admin";

                                if ($vendorId == $ordervendorId) {

                                    $shippingPrice =  $rate->getPrice();
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            return $shippingPrice;
        }
    }
}
