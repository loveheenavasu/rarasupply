<?php 

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Ced
 * @package     Ced_CsDeal
 * @author 		CedCommerce Core Team <coreteam@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
namespace Ced\CsDeal\Controller\Setting;

class Index extends \Ced\CsMarketplace\Controller\Vendor
{
    public function execute()
    {
		if(!$this->_getSession()->getVendorId())
    		return;
    	$resultPage = $this->resultPageFactory->create();		
        $resultPage->getConfig()->getTitle()->set(__('Deal Setting'));
        return $resultPage;
		
    }
}
