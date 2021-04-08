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

namespace Ced\CsCmsPage\Controller\Vcmspage;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Savecms
 * @package Ced\CsCmsPage\Controller\Vcmspage
 */
class Savecms extends \Ced\CsMarketplace\Controller\Vendor
{

    /**
     * @var \Ced\CsCmsPage\Helper\Data
     */
    protected $cmsHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Ced\CsCmsPage\Model\CmspageFactory
     */
    protected $cmspageFactory;

    /**
     * @var \Ced\CsCmsPage\Model\VendorcmsFactory
     */
    protected $vendorcmsFactory;

    /**
     * Savecms constructor.
     * @param \Ced\CsCmsPage\Helper\Data $cmsHelper
     * @param \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory
     * @param \Ced\CsCmsPage\Model\VendorcmsFactory $vendorcmsFactory
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
        \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory,
        \Ced\CsCmsPage\Model\VendorcmsFactory $vendorcmsFactory,
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
        $this->request = $context->getRequest();
        $this->cmspageFactory = $cmspageFactory;
        $this->vendorcmsFactory = $vendorcmsFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->cmsHelper->isEnabled()) {
            return $this->_redirect('*/*/index');
        }
        if (!$this->_getSession()->getVendorId())
            return $this->_redirect('*/*/index');

        $storeId = (int)$this->request->getParam('store', 0);

        $AdminApproval = ($this->csmarketplaceHelper->getStoreConfig('ced_csmarketplace/vcmspage/page_approval', 0));

        if ($AdminApproval == 1) {
            $pageapproval = 0;
        } else {
            $pageapproval = 1;
        }


        $VendorId = $this->_getSession()->getVendorId();
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPostValue();

            try {
                if (sizeof($data) > 0) {
                    $date = date("Y-m-d H:i:s");
                    $identifier = $this->cmsHelper->getVendorShopUrl() . $data['urlkey'];

                    $Vendorcmspage = $this->cmspageFactory->create();
                    try {
                        $Vendorcmspage->setTitle($data['title']);
                        $Vendorcmspage->setIdentifier($identifier);
                        $Vendorcmspage->setIsActive($data['status']);
                        $Vendorcmspage->setContentHeading($data['cheading']);
                        $Vendorcmspage->setContent($data['content']);
                        $Vendorcmspage->setPageLayout($data['layout']);
                        $Vendorcmspage->setLayoutUpdateXml($data['layout_xml']);
                        $Vendorcmspage->setMetaKeywords($data['meta_keywords']);
                        $Vendorcmspage->setMetaDescription($data['meta_description']);
                        $Vendorcmspage->setIsApprove($pageapproval);
                        $Vendorcmspage->setVendorId($VendorId);
                        $Vendorcmspage->setCreationTime($date);
                        $Vendorcmspage->setUpdateTime($date);
                        $Vendorcmspage->setStores($data['store']);
                        $Vendorcmspage->setIsHome($data['is_home']);
                        $Vendorcmspage->save();
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                        return $this->_redirect('*/*/index');
                    }

                    if ($Vendorcmspage->getPageId() != null && $Vendorcmspage->getPageId() > 0) {

                        if (isset($data['store']) && sizeof($data['store']) > 0) {

                            foreach ($data['store'] as $storeId) {
                                try {
                                    if ($this->_getSession()->getVendorId()) {
                                        $Vcmsstore = $this->vendorcmsFactory->create();

                                        $Vcmsstore->setData('page_id', $Vendorcmspage->getPageId());
                                        $Vcmsstore->setData('store_id', $storeId);
                                        $Vcmsstore->setData('vendor_id', $this->_getSession()->getVendorId());
                                        $Vcmsstore->save();
                                    }

                                } catch (\Exception $e) {
                                    $this->messageManager->addErrorMessage($e->getMessage());
                                    return $this->_redirect('*/*/index');
                                }
                            }
                            $this->messageManager->addSuccessMessage(__('You saved this page.'));
                            $this->_redirect('*/*/index');

                        }
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->_redirect('*/*/index');
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Vendor Cms Page'));
        return $resultPage;
    }
}

