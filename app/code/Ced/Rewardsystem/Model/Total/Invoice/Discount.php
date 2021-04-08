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
 * @category    Ced
 * @package     Ced_Rewardsystem
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Rewardsystem\Model\Total\Invoice;

class Discount extends \Magento\Sales\Model\Order\Total\AbstractTotal {
	protected $_storeManager;
	public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager, array $data = []) {
		parent::__construct ( $data );
		$this->_storeManager = $storeManager;
	}
	public function collect(\Magento\Sales\Model\Order\Invoice $invoice) {
		$order = $invoice->getOrder ();
		$baseDiscount = -$order->getRewardsystemBaseAmount();
		$discount = -$order->getRewardsystemDiscount();
		
	if (floatval ( $baseDiscount )) {
				
				$baseDiscount1 = $baseDiscount + $invoice->getBaseDiscountAmount();
				
				$discount1 = $discount + $invoice->getDiscountAmount();
				$invoice->setDiscountAmount ( $baseDiscount1 );
				$invoice->setBaseDiscountAmount ( $baseDiscount1 );
				
				//$invoice->setDiscountDescription ( $invoice->getDiscountDescription()."Reward Discount" );
				$invoice->setBaseGrandTotal ( $invoice->getBaseGrandTotal () +   $baseDiscount);
				$invoice->setGrandTotal ( $invoice->getGrandTotal () + $discount  );
			}
		
		return $this;
	}
}