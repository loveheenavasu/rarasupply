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
 * Class View
 * @package Ced\CsCmsPage\Controller\Page
 */
class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\CsCmsPage\Helper\Page
     */
    protected $cmsHelper;

    /**
     * @var \Ced\CsCmsPage\Model\CmspageFactory
     */
    protected $cmspageFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * View constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\CsCmsPage\Helper\Page $cmsHelper
     * @param \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Ced\CsCmsPage\Helper\Page $cmsHelper,
        \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
    )
    {
        parent::__construct($context);

        $this->_coreRegistry = $registry;
        $this->cmsHelper = $cmsHelper;
        $this->cmspageFactory = $cmspageFactory;
        $this->_request = $context->getRequest();
        $this->vendorFactory = $vendorFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
    }

    /**
     * View CMS page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */

    public function execute()
    {
        $this->_initVendor();
        $pageId = $this->getRequest()->getParam('page_id', $this->getRequest()->getParam('id', false));

        $resultPage = $this->cmsHelper->prepareResultPage($this, $pageId);
        return $resultPage;
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

        $pageId = $this->getRequest()->getParam('page_id', $this->getRequest()->getParam('id', false));

        $pageCollection = $this->cmspageFactory->create()->load($pageId);


        $storeId = (int)$this->_request->getParam('store', 0);
        $vendor = $this->vendorFactory->create()
            ->setStoreId($storeId)
            ->load($pageCollection->getVendorId());

        if (!$this->csmarketplaceHelper->canShow($vendor)) {
            return false;
        }
        $this->_coreRegistry->register('current_vendor', $vendor);

        return $vendor;
    }


}
