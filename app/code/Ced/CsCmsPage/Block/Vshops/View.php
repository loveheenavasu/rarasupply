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

namespace Ced\CsCmsPage\Block\Vshops;

/**
 * Class View
 * @package Ced\CsCmsPage\Block\Vshops
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;

    /**
     * @var
     */
    protected $_vendor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Ced\CsCmsPage\Model\CmspageFactory
     */
    public $cmspageFactory;

    /**
     * @var \Ced\CsCmsPage\Model\VendorcmsFactory
     */
    public $vendorcmsFactory;
    
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * View constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsMarketplace\Model\Vendor\AttributeFactory $attributeFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory
     * @param \Ced\CsCmsPage\Model\VendorcmsFactory $vendorcmsFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\Vendor\AttributeFactory $attributeFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory,
        \Ced\CsCmsPage\Model\VendorcmsFactory $vendorcmsFactory,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $registry;
        $this->vendorFactory = $vendorFactory;
        $this->attributeFactory = $attributeFactory;
        $this->cmspageFactory = $cmspageFactory;
        $this->vendorcmsFactory = $vendorcmsFactory;
        $this->pageConfig = $context->getPageConfig();
        if ($this->getVendor()) {
            $vendor = $this->getVendor();
            if ($vendor->getMetaDescription())
                $this->pageConfig->setDescription($vendor->getMetaDescription());
            if ($vendor->getMetaKeywords())
                $this->pageConfig->setKeywords($vendor->getMetaKeywords());
        }
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getVendor()
    {
        if (!$this->_vendor)
            $this->_vendor = $this->_coreRegistry->registry('current_vendor');
        return $this->_vendor;
    }


    /**
     * @return mixed
     */
    public function getShopurl()
    {
        $shopUrl = $this->vendorFactory->create()->getShopUrlKey($this->getRequest()->getParam('shop_url', ''));
        return ($shopUrl);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getvendordata()
    {
        $shopUrl = $this->vendorFactory->create()->getShopUrlKey($this->getRequest()->getParam('shop_url', ''));
        $vendor = $this->vendorFactory->create()
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->loadByAttribute('shop_url', $shopUrl);
        return ($vendor);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLeftProfileAttributes($storeId = null)
    {
        if ($storeId == null) $storeId = $this->_storeManager->getStore()->getId();
        $attributes = $this->attributeFactory->create()
            ->setStoreId($storeId)
            ->getCollection()
            ->addFieldToFilter('use_in_left_profile', array('gt' => 0))
            ->setOrder('position_in_left_profile', 'ASC');
        $this->_eventManager->dispatch('ced_csmarketplace_left_profile_attributes_load_after', array('attributes' => $attributes));
        return $attributes;
    }

    /**
     * @return mixed
     */
    public function getVendorName()
    {
        return $this->getVendor()->getData('name');
    }

    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        return $this->getVendor()->getData('company_name');
    }

    /**
     * @return mixed
     */
    public function getVendorLogo()
    {
        return $this->getVendor()->getData('company_logo');
    }

    /**
     * @return mixed
     */
    public function getVendorBanner()
    {
        return $this->getVendor()->getData('company_banner');
    }

    /**
     * @return mixed
     */
    public function getTelephone()
    {
        return $this->getVendor()->getData('support_number');
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->getVendor()->getData('company_address');
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->getVendor()->getData('support_email');
    }

    /**
     * @return mixed
     */
    public function getVendorSince()
    {
        return $this->getVendor()->getData('created_at');
    }

    /**
     * @return mixed
     */
    public function getPublicName()
    {
        return $this->getVendor()->getData('public_name');
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        if ($this->getVendor()->getData('facebook_id') != "")
            return 'http://www.facebook.com/' . $this->getVendor()->getData('facebook_id');
        else
            return "";
    }

    /**
     * @return string
     */
    public function getTwitterId()
    {
        if ($this->getVendor()->getData('twitter_id') != "")
            return 'http://www.twitter.com/' . $this->getVendor()->getData('twitter_id');
        else
            return "";
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

}
