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
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCmsPage\Controller\Vcmspage;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Updatecms
 * @package Ced\CsCmsPage\Controller\Vcmspage
 */
class Updatecms extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsCmsPage\Helper\Data
     */
    protected $cmsHelper;

    /**
     * @var \Ced\CsCmsPage\Model\CmspageFactory
     */
    protected $cmspageFactory;

    /**
     * @var \Ced\CsCmsPage\Model\VendorcmsFactory
     */
    protected $vendorcmsFactory;

    /**
     * Updatecms constructor.
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
        $this->cmspageFactory = $cmspageFactory;
        $this->vendorcmsFactory = $vendorcmsFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {

        if (!$this->cmsHelper->isEnabled()) {
            return;
        }
        if (!$this->_getSession()->getVendorId())
            return;


        $VendorId = $this->_getSession()->getVendorId();
        if ($this->getRequest()->getPost() && $this->getRequest()->getParam('page_id') && $this->getRequest()->getParam('page_id') > 0) {
            $page_id = $this->getRequest()->getParam('page_id');

            $Vendorcmspage = $this->cmspageFactory->create()->load($page_id);
            try {
                if ($Vendorcmspage->getId()) {
                    $data = $this->getRequest()->getPostValue();

                    $date = date("Y-m-d H:i:s");
                    $identifier = $this->cmsHelper->getVendorShopUrl() . $data['urlkey'];
                    $Vendorcmspage->setTitle($data['title']);
                    $Vendorcmspage->setIdentifier($identifier);
                    $Vendorcmspage->setIsActive($data['status']);
                    $Vendorcmspage->setContentHeading($data['cheading']);
                    $Vendorcmspage->setContent($data['content']);
                    $Vendorcmspage->setPageLayout($data['layout']);
                    $Vendorcmspage->setLayoutUpdateXml($data['layout_xml']);
                    $Vendorcmspage->setMetaKeywords($data['meta_keywords']);
                    $Vendorcmspage->setMetaDescription($data['meta_description']);
                    $Vendorcmspage->setIsHome($data['is_home']);
                    $Vendorcmspage->setUpdateTime($date);
                    $Vendorcmspage->save();


                    if (isset($data['store']) && count($data['store']) > 0) {
                        foreach ($data['store'] as $storeId) {
                            $Vcmsstore = $this->vendorcmsFactory->create()->load($page_id, 'page_id');

                            $Vcmsstore->setPageId($page_id);
                            $Vcmsstore->setStoreId($storeId);
                            $Vcmsstore->setvendorId($VendorId);
                            $Vcmsstore->setIsHome($data['is_home']);

                            $Vcmsstore->save();
                        }
                        $this->messageManager->addSuccessMessage(__('Cms Page Update Successfully'));
                        $this->_redirect('*/*/index');
                        return;
                    }

                } else {
                    $this->messageManager->addErrorMessage('Fail to update the Cms Page!');
                    $this->_redirect('*/*/index');
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/index');
                return;
            }

        } else {
            $this->messageManager->addErrorMessage('Fail to update the Cms Page!');
            $this->_redirect('*/*/index');
        }


        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Add New CMS Page'));
        return $resultPage;
    }
}


