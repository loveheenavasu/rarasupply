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
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsRfq\Helper;

/**
 * Class Data
 * @package Ced\RequestToQuote\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	
	
	public function __construct(
			\Magento\Framework\App\Helper\Context $context,
			\Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory,
			\Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
			\Ced\CsMarketplace\Model\VendorFactory $vendorFactory
	) {
		
		$this->vsettingsFactory = $vsettingsFactory;
		$this->csmarketplaceHelper = $csmarketplaceHelper;
		$this->vendorFactory = $vendorFactory;
		parent::__construct($context);
	}

	public function isVendorRfqEnable($vendorId){
		
		if($this->getConfigValue('ced_csmarketplace/general/csrfq_enable')){
			if($this->getConfigValue('ced_csmarketplace/general/enable_condition')=='all'){
				return true;
			}else{
				$value = false;
				$vendor_id = $this->csmarketplaceHelper->getTableKey('vendor_id');
				$key_tmp = $this->csmarketplaceHelper->getTableKey('key');
				$setting = $this->vsettingsFactory->create()
				->loadByField([$key_tmp,$vendor_id],['rfq/rfq/active',(int)$vendorId]);
				if($setting) $value = $setting->getValue();
				return $value;
			}
		}
		return false;
	}
	
	public function getConfigValue($path){
		
		return $this->scopeConfig->getValue($path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getSeller($vendorId){
		
		$vendor = $this->vendorFactory->create()->load($vendorId);
		if($vendor->getId()){
			return $vendor->getPublicName();
		}
		return "Admin";
		
	}
	
}