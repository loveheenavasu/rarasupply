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

namespace Ced\CsCmsPage\Controller\Page;

/**
 * Class Index
 * @package Ced\CsCmsPage\Controller\Page
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Ced\CsCmsPage\Helper\Data
     */
    protected $cmsHelper;

    /**
     * @var \Ced\CsCmsPage\Model\CmspageFactory
     */
    protected $cmspageFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;
    
    protected $pageHelper;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Ced\CsCmsPage\Helper\Data $cmsHelper
     * @param \Ced\CsCmsPage\Helper\Page $pageHelper
     * @param \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\CsCmsPage\Helper\Data $cmsHelper,
        \Ced\CsCmsPage\Helper\Page $pageHelper,
        \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
    )
    {
        parent::__construct($context);

        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->cmsHelper = $cmsHelper;
        $this->cmspageFactory = $cmspageFactory;
        $this->vendorFactory = $vendorFactory;
        $this->_request = $context->getRequest();
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->pageHelper = $pageHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {

        if (!$this->cmsHelper->isEnabled()) {
            $this->_redirect('/');
            return;
        }
        try {
            $pageId = '';
            if ($vendor = $this->_initVendor()) {

                if ($vendor->getEntityId() > 0) {
                    $Cms = $this->cmspageFactory->create()->getCollection()
                        ->addFieldToFilter('vendor_id', $vendor->getId())->addFieldToFilter('is_active', '1')->addFieldToFilter('is_approve', '1');

                    if (count($Cms) > 0) {
                        foreach ($Cms as $cmspage) {

                            $CmsStores = $this->cmspageFactory->create()->getCollection()
                                ->addFieldToFilter('page_id', $cmspage->getPageId())->addFieldToFilter('is_home', '1');
                            if (count($CmsStores) > 0) {
                                $pageId = $cmspage->getPageId();
                            } else {
                                $CmsStores = $this->cmspageFactory->create()->getCollection()
                                    ->addFieldToFilter('page_id', $cmspage->getPageId())->addFieldToFilter('is_home', '1');

                                if (count($CmsStores) > 0) {
                                    $pageId = $cmspage->getPageId();
                                }
                            }
                        }
                    }

                    if ($pageId > 0) {
                        $resultPage = $this->pageHelper->prepareResultPage($this, $pageId);
                        return $resultPage;
                    } else {

                        $resultPage = $this->resultPageFactory->create();
                        $update = $resultPage->getLayout()->getUpdate();
                        $update->addHandle('default');
                        $update->addHandle('csmarketplace_vshops_view');
                        $resultPage->getConfig()->getTitle()->set(__($vendor->getPublicName() . " " . ('Shop')));
                        return $resultPage;

                        $this->_redirect('csmarketplace/vshops/view');

                        if ($vendor = $this->_initVendor()) {
                            $resultPage = $this->resultPageFactory->create();
                            $update = $resultPage->getLayout()->getUpdate();
                            $update->addHandle('csmarketplace_vshops_view');
                            $resultPage->getConfig()->getTitle()->set(__($vendor->getPublicName() . " " . ('Shop')));
                            return $resultPage;
                        } else {
                            $this->messageManager->addErrorMessage(__('The Vendor\'s Shop you are trying to access is not available at this moment.'));
                            $this->_redirect('*/*');
                        }
                    }
                }
            } else {
                return $this->_redirect('/');
            }
        } catch (\Exception $e) {
            die($e);
        }
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _initVendor()
    {
        $this->_eventManager->dispatch('csmarketplace_controller_vshops_init_before',
            ['controller_action' => $this]);

        $shopUrl = $this->vendorFactory->create()->getShopUrlKey($this->getRequest()->getParam('shop_url', ''));
        if (!strlen($shopUrl)) {
            return false;
        }
        $storeId = (int)$this->_request->getParam('store', 0);
        $vendor = $this->vendorFactory->create()
            ->setStoreId($storeId)
            ->loadByAttribute('shop_url', $shopUrl);

        if (!$this->csmarketplaceHelper->canShow($vendor)) {
            return false;
        }
        $this->_coreRegistry->register('current_vendor', $vendor);

        try {
            $this->_eventManager->dispatch(
                'csmarketplace_controller_vshops_init_after',
                ['vendor' => $vendor,
                    'controller_action' => $this]

            );
        } catch (Exception $e) {

            return false;
        }

        return $vendor;
    }
}
