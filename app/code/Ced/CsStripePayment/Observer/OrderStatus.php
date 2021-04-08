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
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsStripePayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderStatus implements ObserverInterface
{
	public function __construct(
    	\Ced\CsMarketplace\Model\VordersFactory $vordersFactory
	){
        $this->vordersFactory = $vordersFactory;
    }

	public function execute(Observer $observer) 
    {
    	$order = $observer->getOrder();
     	$orderId = $order->getIncrementId();
     	$payment = $order->getPayment();
     	$vendorOrders = $this->vordersFactory->create()->getCollection()->addFieldToFilter('order_id',$orderId);

        if(!count($vendorOrders->getData()) || $payment->getMethod()!='stripe_payments_sofort' || $payment->getMethod()!='stripe_payments_giropay' || $order->getStatus()!=\Magento\Sales\Model\Order::STATE_PROCESSING)
        	return;

		foreach($vendorOrders as $vendorOrder){
			if($vendorOrder->getPaymentState()==\Ced\CsMarketplace\Model\Vorders::STATE_OPEN){
				$vendorOrder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_PAID);
				$vendorOrder->setRealOrderStatus($order->getStatus());
				$vendorOrder->save();
			}

		}
    }

}