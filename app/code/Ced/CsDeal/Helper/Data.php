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
 * @package     Ced_CsDeal
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Helper;

/**
 * Class Data
 * @package Ced\CsDeal\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigManager;

    /**
     * @var int
     */
    protected $_storeId = 0;

    /**
     * @var
     */
    protected $_statuses;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Ced\CsDeal\Model\ResourceModel\Dealsetting\CollectionFactory
     */
    public $dealsettingCollectionFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * @var \Ced\CsDeal\Model\ResourceModel\Deal\CollectionFactory
     */
    protected $dealCollectionFactory;

    /**
     * @var \Ced\CsDeal\Model\DealsettingFactory
     */
    protected $dealsettingFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    public $_request;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * Data constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsDeal\Model\ResourceModel\Dealsetting\CollectionFactory $dealsettingCollectionFactory
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param \Ced\CsDeal\Model\ResourceModel\Deal\CollectionFactory $dealCollectionFactory
     * @param \Ced\CsDeal\Model\DealsettingFactory $dealsettingFactory
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsDeal\Model\ResourceModel\Dealsetting\CollectionFactory $dealsettingCollectionFactory,
        \Magento\Framework\App\Request\Http $httpRequest,
        \Ced\CsDeal\Model\ResourceModel\Deal\CollectionFactory $dealCollectionFactory,
        \Ced\CsDeal\Model\DealsettingFactory $dealsettingFactory,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_scopeConfigManager = $context->getScopeConfig();
        $this->_coreRegistry = $registry;
        $this->vproductsFactory = $vproductsFactory;
        $this->dealsettingCollectionFactory = $dealsettingCollectionFactory;
        $this->httpRequest = $httpRequest;
        $this->dealCollectionFactory = $dealCollectionFactory;
        $this->dealsettingFactory = $dealsettingFactory;
        $this->_request = $context->getRequest();
        $this->dateTime = $dateTime;
    }

    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }

    /**
     * Get current store
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if ($this->_storeId) $storeId = (int)$this->_storeId;
        else $storeId = isset($_REQUEST['store']) ? (int)$_REQUEST['store'] : null;
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return bool
     */
    public function isApprovalNeeded()
    {
        $approval = $this->_scopeConfigManager->getValue('ced_csmarketplace/csdeal/csdeal_approval', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        if ($approval)
            return true;
        else
            return false;
    }

    /**
     * @param $product
     */
    public function initDeal($product)
    {
        $this->_statuses = $product->getId();
    }

    /**
     * @return bool
     */
    public function isModuleEnable()
    {
        $product = $this->_statuses;
        $store_id = $this->getStore()->getStoreId();
        if ($this->_coreRegistry->registry('current_vendor')) {
            $vendor_id = $this->_coreRegistry->registry('current_vendor')->getId();
        } else {
            if ($this->_coreRegistry->registry('product'))
                $product = $this->_coreRegistry->registry('product')->getId();
            if (!$product)
                $product = $this->_statuses;
            $vendor_id = $this->vproductsFactory->create()->getVendorIdByProduct($product);
        }
        $setting = $this->dealsettingCollectionFactory->create()
            ->addFieldToFilter('vendor_id', $vendor_id)->getFirstItem();

        if (count($setting->getData())) {
            $status = $setting->getStatus();
        } else {
            $status = $this->_scopeConfigManager->getValue('ced_csmarketplace/csdeal/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        }
        if ($status)
            return true;
        else
            return false;
    }

    /**
     * @return bool|void
     */
    public function ShowTimer()
    {
        $product = $this->_statuses;
        if (!$this->isModuleEnable()) return;
        $store_id = $this->getStore()->getStoreId();
        if ($this->_coreRegistry->registry('current_vendor')) {
            $vendor_id = $this->_coreRegistry->registry('current_vendor')->getId();
        } else {
            if ($this->_coreRegistry->registry('product'))
                $product = $this->_coreRegistry->registry('product')->getId();
            if (!$product)
                $product = $this->_statuses;
            $vendor_id = $this->vproductsFactory->create()->getVendorIdByProduct($product);
        }
        $setting = $this->dealsettingCollectionFactory->create()
            ->addFieldToFilter('vendor_id', $vendor_id)->getFirstItem();

        $ShowTimer = '';
        if (count($setting->getData()) > 0) {
            $ShowTimer = $setting->getTimerList();
        } else {
            $ShowTimer = $this->_scopeConfigManager->getValue('ced_csmarketplace/csdeal/csdeal_timer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        }

        switch ($ShowTimer) {
            case 'list':
                if ($this->httpRequest->getFullActionName() == 'csmarketplace_vshops_view') {
                    return true;
                }
                return false;
                break;
            case 'view':
                if ($this->httpRequest->getFullActionName() == 'catalog_product_view') {
                    return true;
                }
                return false;
                break;

            default:
                return true;
                break;
        }
    }

    /**
     * @return mixed|void
     */
    public function ShowDeal()
    {
        $product = $this->_statuses;
        if (!$this->isModuleEnable())
            return;
        $store_id = $this->getStore()->getStoreId();
        if ($this->_coreRegistry->registry('current_vendor')) {
            $vendor_id = $this->_coreRegistry->registry('current_vendor')->getId();
        } else {
            if ($this->_coreRegistry->registry('product'))
                $product = $this->_coreRegistry->registry('product')->getId();
            if (!$product)
                $product = $this->_statuses;
            $vendor_id = $this->vproductsFactory->create()->getVendorIdByProduct($product);
        }
        $setting = $this->dealsettingCollectionFactory->create()
            ->addFieldToFilter('vendor_id', $vendor_id)->getFirstItem();
        if (count($setting->getData())) {
            $ShowDeal = $setting->getDealList();
        } else {
            $ShowDeal = $this->_scopeConfigManager->getValue('ced_csmarketplace/csdeal/csdeal_show', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        }
        return $ShowDeal;
    }

    /**
     * @param $product_id
     */
    public function getDealEnd($product_id)
    {
        if (!$this->isModuleEnable()) return;
        $deal = $this->dealCollectionFactory->create()
            ->addFieldToFilter('status', \Ced\CsDeal\Model\Status::STATUS_ENABLED)
            ->addFieldToFilter('product_id', $product_id)
            ->getFirstItem();
        return $deal->getEndDate();
    }

    /**
     * @param $product_id
     */
    public function getStartDate($product_id)
    {
        if (!$this->isModuleEnable()) return;
        $deal = $this->dealCollectionFactory->create()
            ->addFieldToFilter('status', \Ced\CsDeal\Model\Status::STATUS_ENABLED)
            ->addFieldToFilter('product_id', $product_id)
            ->getFirstItem();

        return $deal->getStartDate();
    }

    /**
     * @return mixed
     */
    public function getDealText()
    {
        $product = $this->_statuses;
        $store_id = $this->getStore()->getStoreId();
        if ($this->_coreRegistry->registry('current_vendor')) {
            $vendor_id = $this->_coreRegistry->registry('current_vendor')->getId();
        } else {
            if ($this->_coreRegistry->registry('product'))
                $product = $this->_coreRegistry->registry('product')->getId();
            if (!$product)
                $product = $this->_statuses;
            $vendor_id = $this->vproductsFactory->create()->getVendorIdByProduct($product);
        }
        $setting = $this->dealsettingFactory->create()->load($vendor_id, 'vendor_id');
        if ($setting->getDealMessage()) {
            $dealtext = $setting->getDealMessage();
        } else {
            $dealtext = $this->_scopeConfigManager->getValue('ced_csmarketplace/csdeal/csdeal_default_text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        }
        if ($dealtext)
            return $dealtext;
    }

    /**
     * @param $deal
     * @return bool
     */
    public function getDealDay($deal)
    {
        $days = $deal->getDays();
        if ($days) {
            return true;
        } else {
            $specifiday = $deal->getSpecificdays();
            $specifiday = explode(',', $specifiday);
            switch (date("l")) {
                case 'Monday':
                    if (in_array('mon', $specifiday))
                        return true;
                    break;
                case 'Tuesday':
                    if (in_array('tue', $specifiday))
                        return true;
                    break;
                case 'Wednesday':
                    if (in_array('wed', $specifiday))
                        return true;
                    break;
                case 'Thursday':
                    if (in_array('thu', $specifiday))
                        return true;
                    break;
                case 'Friday':
                    if (in_array('fri', $specifiday))
                        return true;
                    break;
                case 'Saturday':
                    if (in_array('sat', $specifiday))
                        return true;
                    break;
                case 'Sunday':
                    if (in_array('sun', $specifiday))
                        return true;
                    break;
                default:
                    return true;
                    break;
            }
        }
    }

    /**
     * @param $product_id
     * @return bool
     * @throws \Exception
     */
    public function canShowDeal($product_id)
    {
        $deal = $this->dealCollectionFactory->create()
            ->addFieldToFilter('admin_status', \Ced\CsDeal\Model\Deal::STATUS_APPROVED)
            ->addFieldToFilter('status', \Ced\CsDeal\Model\Status::STATUS_ENABLED)
            ->addFieldToFilter('product_id', $product_id)
            ->getFirstItem();

        if (!empty($deal->getData())) {
            $endDate = $deal->getEndDate();
            $startDate = $deal->getStartDate();
            if ($endDate == $startDate) {
                return false;
            }
            $timezone = $this->_scopeConfigManager->getValue('general/locale/timezone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $tz_object = new \DateTimeZone($timezone);
            $datetime = new \DateTime();
            $datetime->setTimezone($tz_object);
            $currentDate = $datetime->format('Y-m-d H:i:s');
            if ((strtotime($currentDate) >= strtotime($startDate) || strtotime($currentDate) <= strtotime($endDate))) {
                switch ($this->ShowDeal()) {
                    case 'list':
                        if ($this->httpRequest->getFullActionName() == 'csmarketplace_vshops_view') {
                            return true;
                        }
                        return false;
                        break;
                    case 'view':
                        if ($this->httpRequest->getFullActionName() == 'catalog_product_view') {
                            return true;
                        }
                        return false;
                        break;

                    default:
                        return true;
                        break;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return $this->_scopeConfigManager->getValue('ced_csmarketplace/csdeal/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }

}
