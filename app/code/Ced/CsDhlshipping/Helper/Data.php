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
  * @package   Ced_Fastway
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
   
namespace Ced\CsDhlshipping\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
   
    
  protected $_objectManager;

  protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_objectManager = $objectInterface;
        $this->_scopeConfig = $scopeConfig;
    
    }
    
    
    public function isEnabled($storeId = 0) {

        if ($storeId == 0)
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        
        return  $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ced_csdhlshipping/general/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
    }
    
        
    
    
    
    
   
    
}