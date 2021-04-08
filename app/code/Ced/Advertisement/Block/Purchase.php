<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Advertisement\Block;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Customer dashboard block
 *
 * @api
 * @since 100.0.2
 */
class Purchase extends \Magento\Framework\View\Element\Template
{
    protected $_cartHelper;
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Catalog\Model\Product $product,
        \Ced\Advertisement\Model\Blocks $blocks,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Ced\Advertisement\Model\Positions $position,
        \Ced\Advertisement\Model\Purchased $purchased,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->_objectManager = $objectManager;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_position = $position;
        $this->_checkoutSession = $checkoutSession;
        $this->blocks = $blocks;
        $this->purchased = $purchased;
        $this->_customerSession = $session;
        $this->_cartHelper = $cartHelper;
        parent::__construct($context, $data);
    }

    public function getPlanColl(){   
        $collection = [];
        $proId = [];
        /*plans alreday purchased and number of ads allowed at any position*/
        $purchasedColl = $this->purchased->getCollection()->addFieldToFilter('customer_id',$this->_customerSession->getCustomerId())->addFieldToFilter('status',1);        
        $customer_id = $this->_customerSession->getId();
        $adsAllowedPerPosition = $this->scopeConfig->getValue('advertisement/ads_settings/ad_qty', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $adsPerPositions = [];
        foreach ($purchasedColl as $purchased) {            
            if(isset($adsPerPositions[$purchased['position_identifier']])){
                $adsPerPositions[$purchased['position_identifier']] = $adsPerPositions[$purchased['position_identifier']]++;
            }else{
                $adsPerPositions[$purchased['position_identifier']] = 1;
            }    
            if(($purchased->getCustomerId() == $customer_id) || ($adsPerPositions[$purchased['position_identifier']] >= $adsAllowedPerPosition)){
                $proId[] = $purchased->getPlanId();
            }                    
        }

        /*Plans already available in Cart*/
        $allItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        
        foreach ($allItems as $item) {
            $proId[] = $item->getProductId();
        }
        $proId = array_unique($proId);

        /*Allowed plans*/

        $connection = $this->resource->getConnection();
        $cataloginventory_stock_item_table = $this->resource->getTableName('cataloginventory_stock_item');
        $ced_advertisement_positions_table = $this->resource->getTableName('ced_advertisement_positions');
        if(count($proId)){
           $collection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
                                ->addFieldToFilter('entity_id', ['nin' => $proId])
                                ->addAttributeToSelect('*')
                                ->addAttributeToFilter('is_plan',1)
                                ->addAttributeToFilter('status',1); 
        } else {
            $collection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
                                ->addAttributeToSelect('*')
                                ->addAttributeToFilter('is_plan',1)
                                ->addAttributeToFilter('status',1);
        }

        if(count($collection)){ 
            $collection->getSelect()->joinLeft($cataloginventory_stock_item_table, '`e`.entity_id='.$cataloginventory_stock_item_table .'.product_id AND `is_in_stock`=\'1\'', ['is_in_stock']);
            $collection->getSelect()->joinLeft($ced_advertisement_positions_table , '`e`.position_identifier='.$ced_advertisement_positions_table .'.identifier', ['position_name']);
        }
        return $collection;
    }

    public function getBlockColl(){
        $customer_id = $this->_customerSession->getId();
        if($customer_id){
            $data = $this->blocks->getCollection()->addFieldToFilter('status',1)->addFieldToFilter('customer_id', $customer_id);
            return $data;
        }
    }

    public function getAddToCartUrl($product, $additional = [])
    {
        if (!$product->getTypeInstance()->isPossibleBuyFromList($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }

        return '#';
    }

    /**
     * Check Product has URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }
}


