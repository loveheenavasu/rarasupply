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
  * @package     Ced_CsCmsPage
  * @author   CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsCmsPage\Block\Adminhtml\Cmspage\Grid\Renderer\Action;

class UrlBuilder
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $frontendUrlBuilder;
    public $_storeManager;
    /**
     * @param \Magento\Framework\UrlInterface $frontendUrlBuilder
     */
    public function __construct(\Magento\Framework\UrlInterface $frontendUrlBuilder,
    		\Magento\Store\Model\StoreManagerInterface $storeManager
)
    {
    	$this->_storeManager=$storeManager;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
    }

    /**
     * Get action url
     *
     * @param string $routePath
     * @param string $scope
     * @param string $store
     * @return string
     */
    public function getUrlmbm($routePath, $scope, $store)
    {
    	$this->frontendUrlBuilder->setScope($scope);
    	$href = $this->frontendUrlBuilder->getUrl(
    			$routePath,
    			[
    			'_current' => false,
    			'_query' => [StoreResolverInterface::PARAM_NAME => $store]
    			]
    	);
    
    	return $href;
    }
    
    
    public function getUrl($routePath, $scope, $store)
    {
    	$href = ($this->_storeManager->getStore()->getBaseUrl().$routePath.'/?___store='.$store);
    	
        return $href; 
    }
}
