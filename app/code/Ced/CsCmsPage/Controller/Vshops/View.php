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

namespace Ced\CsCmsPage\Controller\Vshops;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class View
 * @package Ced\CsCmsPage\Controller\Vshops
 */
class View extends \Magento\Framework\App\Action\Action
{

    /**
     * Initialize requested vendor object
     *
     * @return Ced_CsMarketplace_Model_Vendor
     */
    protected $_coreRegistry;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Acl
     */
    protected $aclHelper;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    )
    {
        parent::__construct($context);

        $this->_coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->aclHelper = $aclHelper;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->vendorFactory = $vendorFactory;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _initVendor()
    {

        $this->_eventManager->dispatch('csmarketplace_controller_vshops_init_before', array('controller_action' => $this));

        if (!$this->aclHelper->isEnabled())
            return false;

        $shopUrl = $this->getRequest()->getParam('shop_url');
        if (!strlen($shopUrl)) {
            return false;
        }
        $storeId = $this->csmarketplaceHelper->getStore()->getId();

        $vendor = $this->vendorFactory->create()
            ->setStoreId($storeId)->loadByAttribute('shop_url', $shopUrl);

        if (!$this->csmarketplaceHelper->canShow($vendor)) {
            return false;
        } else if (!$this->csmarketplaceHelper->isShopEnabled($vendor)) {
            return false;
        }
        $this->_coreRegistry->register('current_vendor', $vendor);
        try {
            $this->_eventManager->dispatch(
                'csmarketplace_controller_vshops_init_after',
                array(
                    'vendor' => $vendor,
                    'controller_action' => $this
                )
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('Invalid login or password.'));
        }

        return $vendor;
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if ($vendor = $this->_initVendor()) {
            if ($this->_coreRegistry->registry('current_category') == null) {
                $category = $this->categoryFactory->create()
                    ->setStoreId($this->csmarketplaceHelper->getStore()->getId())
                    ->load($this->csmarketplaceHelper->getRootId());
            }
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__($vendor->getPublicName() . " " . ('Shop')));
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('The Vendor\'s Shop you are trying to access is not available at this moment.'));
            $this->_redirect('*/*');
        }

    }
}