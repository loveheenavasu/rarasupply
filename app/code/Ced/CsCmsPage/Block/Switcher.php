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
 * @package     Ced_CsCmsPage
 * @author   CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCmsPage\Block;

/**
 * Class Switcher
 * @package Ced\CsCmsPage\Block
 */
class Switcher extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    protected $_storeIds;

    /**
     * @var string
     */
    protected $_storeVarName = 'store';

    /**
     * @var bool
     */
    protected $_hasDefaultOption = true;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * Switcher constructor.
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\GroupFactory $groupFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     */
    public function __construct(
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\GroupFactory $groupFactory,
        \Magento\Framework\View\Element\Template\Context $context
    )
    {
        parent::__construct($context);

        $this->websiteFactory = $websiteFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->registry = $registry;
        $this->groupFactory = $groupFactory;

        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultStoreName(__('Default Values'));

    }

    /**
     * Deprecated
     */
    public function getWebsiteCollection()
    {
        $collection = $this->websiteFactory->create()->getResourceCollection();

        $websiteIds = $this->getWebsiteIds();
        if (!is_null($websiteIds)) {
            $collection->addIdFilter($this->getWebsiteIds());
        }

        return $collection->load();
    }

    /**
     * Get websites
     *
     * @return array
     */
    public function getWebsites()
    {
        $websites = $this->websiteFactory->create()->getCollection()->toOptionHash();
        $websiteIds = $this->vproductsFactory->create()->getAllowedWebsiteIds();
        if ($this->registry->registry('current_product') != null) {
            $product = $this->registry->registry('current_product');
            $prowebsites = $product->getWebsiteIds();
            if (is_array($prowebsites) && count($prowebsites)) {
                $websiteIds = array_unique(array_intersect($websiteIds, $prowebsites));
            }
        }
        if ($websiteIds) {
            foreach ($websites as $websiteId => $website) {
                if (!in_array($websiteId, $websiteIds)) {
                    unset($websites[$websiteId]);
                } else {
                    $websites[$websiteId] = $this->websiteFactory->create()->load($websiteId);
                }
            }
        }

        return $websites;
    }

    /**
     * @param $website
     * @return \Magento\Store\Model\ResourceModel\Group\Collection
     */
    public function getGroupCollection($website)
    {
        if (!$website instanceof \Magento\Store\Model\Website) {

            $website = $this->websiteFactory->create()->load($website);
        }

        return $website->getGroupCollection();
    }

    /**
     * Get store groups for specified website
     *
     * @return array
     */
    public function getStoreGroups($website)
    {
        if (!$website instanceof \Magento\Store\Model\Website) {
            $website = Mage::app()->getWebsite($website);
        }
        return $website->getGroups();
    }

    /**
     * Deprecated
     */
    public function getStoreCollection($group)
    {

        if (!$group instanceof \Magento\Store\Model\Group) {
            $group = $this->groupFactory->create()->load($group);
        }
        $stores = $group->getStoreCollection();
        $_storeIds = $this->getStoreIds();
        if (!empty($_storeIds)) {
            $stores->addIdFilter($_storeIds);
        }
        return $stores;
    }

    /**
     * Get store views for specified store group
     *
     * @return array
     */
    public function getStores($group)
    {

        if (!$group instanceof \Magento\Store\Model\Group) {
            $group = Mage::app()->getGroup($group);
        }
        $stores = $group->getStores();
        if ($storeIds = $this->getStoreIds()) {
            foreach ($stores as $storeId => $store) {
                if (!in_array($storeId, $storeIds)) {
                    unset($stores[$storeId]);
                }
            }
        }
        return $stores;
    }

    /**
     * @return mixed|string
     */
    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current' => true, $this->_storeVarName => null, '_secure' => true, '_nosid' => true));
    }

    /**
     * @param $varName
     * @return $this
     */
    public function setStoreVarName($varName)
    {
        $this->_storeVarName = $varName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->getRequest()->getParam($this->_storeVarName);
    }

    /**
     * @param $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * @return array
     */
    public function getStoreIds()
    {
        return $this->_storeIds;
    }

    /**
     * @return bool
     */
    public function isShow()
    {
        return true;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml();
    }

    /**
     * Set/Get whether the switcher should show default option
     *
     * @param bool $hasDefaultOption
     * @return bool
     */
    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) {
            $this->_hasDefaultOption = $hasDefaultOption;
        }
        return $this->_hasDefaultOption;
    }
}
