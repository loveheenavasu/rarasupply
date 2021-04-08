<?php

/**
 * CedCommerce
  *
  * NOTICE OF LICENSE
  *
  * This source file is subject to the Academic Free License (AFL 3.0)
  * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
  * It is also available through the world-wide-web at this URL:
  * http://opensource.org/licenses/afl-3.0.php
  *
  * @category    Ced
  * @package     Ced_CsFedexShipping
  * @author       CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */
namespace Ced\CsFedexShipping\Helper;

/**
 * Configuration data of carrier
 */
 class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	

	protected $_helper;
	protected $_scopeConfig;
	
	protected $_objectManager;
	protected $_vsettingsFactory;
	
	public function __construct(\Magento\Framework\App\Helper\Context $context,
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Magento\Framework\ObjectManagerInterface $objectManager
	)
	{
		$this->_scopeConfig = $scopeConfig;
		$this->_objectManager = $objectManager;
		parent::__construct($context);
		$this->_helper  = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data');
	}
	
	public function isEnabled($storeId=0){
		
		if($storeId == 0)
			$storeId = $this->_helper->getStore()->getId();
		return $this->_helper->getStoreConfig('ced_csfedexshipping/general/active', $storeId);
	}
	
	
}