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
namespace Ced\CsStripePayment\Helper;

use StripeIntegration\Payments\Helper\Logger;
use StripeIntegration\Payments\Exception\WebhookException;

class Webhooks extends \StripeIntegration\Payments\Helper\Webhooks
{
	public function __construct(
		\StripeIntegration\Payments\Logger\WebhooksLogger $webhooksLogger,
        \Ced\CsStripePayment\Model\PaymentIntent $cedPaymentIntent,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Ced\CsStripePayment\Model\ManagedFactory $managedFactory,
        \Ced\CsMarketplace\Model\VsettingsFactory $vsettingfactory,
        \Ced\CsStripePayment\Model\StandaloneFactory $standAloneFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    	$this->webhooksLogger = $webhooksLogger;
        $this->cedPaymentIntent = $cedPaymentIntent;
        $this->vsettingfactory = $vsettingfactory;
        $this->managedFactory = $managedFactory;
        $this->vordersFactory = $vordersFactory;
        $this->standAloneFactory = $standAloneFactory;
        $this->_scopeConfig = $scopeConfig;
    }

	// Called after a source.chargable event
    public function createTransfer($order, $chargeId)
    {
        $orderId = $order->getIncrementId();
        $payment = $order->getPayment();
        //$this->webhooksLogger->addInfo($payment->getMethod().'----'.count($vendorOrders->getData()));
        if (!$payment)
            throw new WebhookException("Could not load payment method for order #$orderId");
        /* custom code for vendor wise transfer starts */
        try{
            $vendorOrders = $this->vordersFactory->create()->getCollection()->addFieldToFilter('order_id',$orderId);
            if(!count($vendorOrders->getData()) || ($payment->getMethod()!='stripe_payments_sofort' && $payment->getMethod()!='stripe_payments_giropay')){
                $this->webhooksLogger->addInfo('There is no vendor orders for order #$orderId');
                return;
            }
        	$account_type = $this->_scopeConfig->getValue ( 'ced_csmarketplace/csstripe/account_type' );

        	foreach($vendorOrders as $vendorOrder)
            {
                $netVendorEarn = $vendorOrder['order_total'] - $vendorOrder ['shop_commission_fee'];
                $vendorId = $vendorOrder['vendor_id'];	
                $vsetting = $this->vsettingfactory->create()->getCollection()->addFieldToFilter('vendor_id',$vendorId)->addFieldToFilter('group','payment')->addFieldToFilter('key','payment/vstripe/active');
                $this->webhooksLogger->addInfo($netVendorEarn.'----'.count($vsetting->getData()).' VendorId -'.$vendorId);
                if(!count($vsetting->getData()))
                	continue;

                $stripe_acc = '';
                if ($account_type == 'managed'){
                    $check_acc = $this->managedFactory->create()->load($vendorId,'vendor_id');
                    
                    if (count($check_acc->getData()))
                        $stripe_acc = $check_acc['account_id'];
                }else{
                    $standAlone = $this->standAloneFactory->create()->load($vendorId,'vendor_id');
                    /**
                     * get token, if Mode is set to standalone
                     */
                    if(count($standAlone->getData()))
                    	$stripe_acc = $standAlone['stripe_user_id'];
                }
                if($stripe_acc){
                    try {
                        $transfer =   \Stripe\Transfer::create([
                            "amount" => $netVendorEarn*100, // amount in cents
                            "currency" => $order->getOrderCurrencyCode(),
                            "source_transaction" => $chargeId,
                            "destination" => $stripe_acc,
                            "transfer_group" => $orderId 
                        ]);
                    }catch ( \Stripe\Exception\CardException $e ) {
                    	//echo $e->getMessage();die;
                    	$this->webhooksLogger->addInfo($e->getMessage());
                        //throw new \Magento\Framework\Exception\LocalizedException(__( $e->getMessage()));
                    }
                    $transferId = $transfer->id;
                    
                    $event_data_array = [
                        'transaction_id' => $transferId,
                        'vendor_id' => $vendorId,
                        'amount' => $netVendorEarn,
                        'order_id' => $orderId
                    ];
                    $this->cedPaymentIntent->saveVpayment($event_data_array, $vendorId, 2 );
                    $vendorOrder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_PAID);
					$vendorOrder->setRealOrderStatus($order->getStatus());
					$vendorOrder->save();
                } 
            }
        }catch(\Exception $e){
        	$this->webhooksLogger->addInfo($e->getMessage());
        	//throw new \Magento\Framework\Exception\LocalizedException ( __ ( $e->getMessage () ) );
        }

        /* custom code ends */
    }
}