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

namespace Ced\CsCmsPage\Controller\Vblock;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Saveblock
 * @package Ced\CsCmsPage\Controller\Vblock
 */
class Saveblock extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsCmsPage\Helper\Data
     */
    protected $cmsHelper;

    /**
     * @var \Ced\CsCmsPage\Model\VendorblockFactory
     */
    protected $vendorblockFactory;

    /**
     * @var \Ced\CsCmsPage\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * Saveblock constructor.
     * @param \Ced\CsCmsPage\Helper\Data $cmsHelper
     * @param \Ced\CsCmsPage\Model\VendorblockFactory $vendorblockFactory
     * @param \Ced\CsCmsPage\Model\BlockFactory $blockFactory
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Ced\CsCmsPage\Helper\Data $cmsHelper,
        \Ced\CsCmsPage\Model\VendorblockFactory $vendorblockFactory,
        \Ced\CsCmsPage\Model\BlockFactory $blockFactory,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );

        $this->cmsHelper = $cmsHelper;
        $this->vendorblockFactory = $vendorblockFactory;
        $this->blockFactory = $blockFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId())
            return;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPostValue();

            $VendorId = $this->_getSession()->getVendorId();
            try {

                $AdminApproval = ($this->csmarketplaceHelper->getStoreConfig('ced_csmarketplace/vcmspage/block_approval', 0));

                if ($AdminApproval == 1) {
                    $pageapproval = 0;
                } else {
                    $pageapproval = 1;
                }

                if (sizeof($data) > 0) {

                    $identifier = $this->cmsHelper->getVendorShopUrl() . $data['identifier'];

                    $date = date("Y-m-d H:i:s");
                    $Vendorblock = $this->blockFactory->create();
                    $Vendorblock->setTitle($data['title']);
                    $Vendorblock->setIdentifier($identifier);
                    $Vendorblock->setIsActive($data['status']);
                    $Vendorblock->setContent($data['content']);
                    $Vendorblock->setIsApprove($pageapproval);
                    $Vendorblock->setVendorId($VendorId);
                    $Vendorblock->setCreationTime($date);
                    $Vendorblock->setUpdateTime($date);
                    $Vendorblock->save();

                    if ($Vendorblock->getBlockId() != null && $Vendorblock->getBlockId() > 0) {

                        if (isset($data['store']) && sizeof($data['store']) > 0) {
                            foreach ($data['store'] as $storeId) {
                                if ($this->_getSession()->getVendorId()) {
                                    $Vblockstore = $this->vendorblockFactory->create();
                                    $Vblockstore->setBlockId($Vendorblock->getBlockId());
                                    $Vblockstore->setStoreId($storeId);
                                    $Vblockstore->setvendorId($this->_getSession()->getVendorId());
                                    $Vblockstore->save();
                                }

                            }
                            $this->messageManager->addSuccessMessage(__('Block Page Created Successfully'));
                            $this->_redirect('*/*/index');
                        }
                    }
                }
            } catch (\Exception $e) {

                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/new');
                return;
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Vendor Static Block'));
        return $resultPage;
    }
}

