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
  * @package   Ced_Advertisement
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\Advertisement\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(\Magento\Framework\App\Helper\Context $context
    ) {    
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    public function isModuleEnable(){
      return $this->scopeConfig->getValue('advertisement/enable_ads/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }  
  
}

