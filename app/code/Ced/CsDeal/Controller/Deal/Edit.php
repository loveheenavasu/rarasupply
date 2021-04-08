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
 * @package     Ced_CsDeal
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Controller\Deal;
use Magento\Customer\Model\Session;
use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\UrlFactory;

class Edit extends \Ced\CsMarketplace\Controller\Vendor
{
    
	protected $_scopeConfig;
	protected $_storeManager;
	protected $_httpRequest;
	protected $resultPageFactory;
    
    public function execute() {
		if(!$this->_getSession()->getVendorId()) 
    		return;
    	$resultPage = $this->resultPageFactory->create();
    	$resultRedirect = $this->resultRedirectFactory->create();
    	$resultPage->getConfig()->getTitle()->set(__('Edit Deal'));
    	return $resultPage;
    }
}