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

namespace Ced\CsCmsPage\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package Ced\CsCmsPage\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_NODE_PAGE_TEMPLATE_FILTER = 'global/cms/page/tempate_filter';

    const XML_NODE_BLOCK_TEMPLATE_FILTER = 'global/cms/block/tempate_filter';

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

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
     * Data constructor.
     * @param Session $customerSession
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\GroupFactory $groupFactory
     * @param Context $context
     */
    public function __construct(
        Session $customerSession,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\GroupFactory $groupFactory,
        Context $context
    )
    {
        parent::__construct($context);

        $this->session = $customerSession;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->websiteFactory = $websiteFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->registry = $registry;
        $this->groupFactory = $groupFactory;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEnabled()
    {
        return ($this->csmarketplaceHelper->getStoreConfig('ced_csmarketplace/general/cscmspage', 0));
    }


    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isApproved()
    {
        return ($this->csmarketplaceHelper->getStoreConfig('ced_csmarketplace/vcmspage/page_approval', 0));
    }

    /**
     *
     * Fetch All Websites and Stores ...
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
     * @param $group
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
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
     * Retrieve Template processor for Page Content
     *
     * @return Varien_Filter_Template
     */
    public function getPageTemplateProcessor()
    {
        $model = (string)Mage::getConfig()->getNode(self::XML_NODE_PAGE_TEMPLATE_FILTER);
        return Mage::getModel($model);
    }

    /**
     * Retrieve Template processor for Block Content
     *
     * @return Varien_Filter_Template
     */
    public function getBlockTemplateProcessor()
    {
        $model = (string)Mage::getConfig()->getNode(self::XML_NODE_BLOCK_TEMPLATE_FILTER);
        return Mage::getModel($model);
    }

    /**
     * @return Ced_CsCmsPage_Model_Data
     */
    public function getVendorId()
    {
        $vid = '';
        if (Mage::app()->getRequest()->getParam('vid') > 0) {
            $vid = Mage::app()->getRequest()->getParam('vid');
        }
        /*elseif(Mage::getSingleton('customer/session')->getVendorId()>0){
            $vid = Mage::getSingleton('customer/session')->getVendorId();
        }*/
        return $vid;
    }

    /**
     * getVendor Shop Page Url
     *
     * @return Ced_CsCmsPage_Model_Data
     *
     */
    public function getVendorShopUrl()
    {
        $vdata = $this->session->getVendor();
        $shopurl = $vdata['shop_url'];

        return 'vendorshop/' . $shopurl . '/';
    }

    /**
     * @return Pending Approval Cms Page Count
     */
    public function getApprovalVendorCms()
    {
        $VendorCms = Mage::getModel('cscmspage/cmspage')->getCollection()
            ->addFieldToFilter('is_approve', '0');

        return count($VendorCms);

    }

    /**
     * @return Pending Approval Block Page Count
     */
    public function getApprovalVendorBlock()
    {

        $VendorCms = Mage::getModel('cscmspage/block')->getCollection()
            ->addFieldToFilter('is_approve', '0');

        return count($VendorCms);
    }
}

?>