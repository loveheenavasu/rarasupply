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

namespace Ced\CsCmsPage\Controller\Adminhtml\Vcmspage;

use Magento\Backend\App\Action;

/**
 * Class Save
 * @package Ced\CsCmsPage\Controller\Adminhtml\Vcmspage
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Ced\CsCmsPage\Model\CmspageFactory
     */
    protected $cmspageFactory;

    /**
     * @var \Ced\CsCmsPage\Model\VendorcmsFactory
     */
    protected $vendorcmsFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Ced\CsCmsPage\Helper\Data
     */
    protected $cmsHelper;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory
     * @param \Ced\CsCmsPage\Model\VendorcmsFactory $vendorcmsFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsCmsPage\Helper\Data $cmsHelper
     * @param \Magento\Backend\Model\Session $backendSession
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory,
        \Ced\CsCmsPage\Model\VendorcmsFactory $vendorcmsFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsCmsPage\Helper\Data $cmsHelper,
        \Magento\Backend\Model\Session $backendSession
    )
    {
        parent::__construct($context);

        $this->dataProcessor = $dataProcessor;
        $this->cmspageFactory = $cmspageFactory;
        $this->vendorcmsFactory = $vendorcmsFactory;
        $this->vendorFactory = $vendorFactory;
        $this->cmsHelper = $cmsHelper;
        $this->backendSession = $backendSession;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Cms::save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $data = $this->dataProcessor->filter($data);
            $Vendorcmspage = $this->cmspageFactory->create();

            $id = $this->getRequest()->getParam('page_id');
            if ($id) {
                $Vendorcmspage->load($id);
                $VendorId = $Vendorcmspage->getVendorId();

                $vendorCollection = $this->vendorFactory->create()->load($VendorId);
                $identifier = 'vendor-shop/' . $vendorCollection->getShopUrl() . '/' . $data['identifier'];

                $date = date("Y-m-d H:i:s");
                $Vendorcmspage->setTitle($data['title']);
                $Vendorcmspage->setIsActive($data['is_active']);
                $Vendorcmspage->setContentHeading($data['content_heading']);
                $Vendorcmspage->setContent($data['content']);
                $Vendorcmspage->setPageLayout($data['page_layout']);
                $Vendorcmspage->setRootTemplate($data['custom_root_template']);
                $Vendorcmspage->setLayoutUpdateXml($data['layout_update_xml']);
                $Vendorcmspage->setMetaKeywords($data['meta_keywords']);
                $Vendorcmspage->setMetaDescription($data['meta_description']);
                $Vendorcmspage->setUpdateTime($date);
                $Vendorcmspage->save();

                if (isset($data['stores']) && sizeof($data['stores']) > 0) {

                    foreach ($data['stores'] as $storeId) {
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
                    return $this->_redirect('*/*/index');

                }

            } else {

                $identifier = $this->cmsHelper->getVendorShopUrl() . $data['identifier'];

                $Vendorcmspage->setData($data);

                $date = date("Y-m-d H:i:s");
                $Vendorcmspage->setTitle($data['title']);
                $Vendorcmspage->setIdentifier($identifier);
                $Vendorcmspage->setIsActive($data['is_active']);
                $Vendorcmspage->setContentHeading($data['content_heading']);
                $Vendorcmspage->setContent($data['content']);
                $Vendorcmspage->setPageLayout($data['page_layout']);
                $Vendorcmspage->setRootTemplate($data['custom_root_template']);
                $Vendorcmspage->setLayoutUpdateXml($data['layout_update_xml']);
                $Vendorcmspage->setMetaKeywords($data['meta_keywords']);
                $Vendorcmspage->setMetaDescription($data['meta_description']);
                $Vendorcmspage->setUpdateTime($date);
                $Vendorcmspage->save();
                try {

                    if ($Vendorcmspage->getPageId() != null && $Vendorcmspage->getPageId() > 0) {

                        if (isset($data['stores']) && sizeof($data['stores']) > 0) {

                            foreach ($data['stores'] as $storeId) {

                                $Vcmsstore = $this->vendorcmsFactory->create();
                                $Vcmsstore->setPageId($Vendorcmspage->getPageId());
                                $Vcmsstore->setStoreId($storeId);
                                $Vcmsstore->setVendorId($this->_getSession()->getVendorId());

                                $Vcmsstore->save();
                            }

                            $this->messageManager->addSuccessMessage(__('You saved this page.'));
                            $this->_redirect('*/*/');
                        }
                    }

                    $this->_eventManager->dispatch(
                        'cms_page_prepare_save',
                        ['page' => $Vendorcmspage, 'request' => $this->getRequest()]
                    );

                    if (!$this->dataProcessor->validate($data)) {
                        return $resultRedirect->setPath('*/*/edit', ['page_id' => $Vendorcmspage->getId(), '_current' => true]);
                    }

                    try {

                        $this->messageManager->addSuccessMessage(__('You saved this page.'));
                        $this->backendSession->setFormData(false);
                        if ($this->getRequest()->getParam('back')) {
                            return $resultRedirect->setPath('*/*/edit', ['page_id' => $Vendorcmspage->getId(), '_current' => true]);
                        }
                        return $resultRedirect->setPath('*/*/');
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    } catch (\RuntimeException $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    } catch (\Exception $e) {
                        $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
                    }

                    $this->_getSession()->setFormData($data);
                    return $resultRedirect->setPath('*/*/edit', ['page_id' => $this->getRequest()->getParam('page_id')]);

                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
                }
                return $resultRedirect->setPath('*/*/');
            }
        }
    }
}
                  
                  
                  
                  
                  
                  
                  
  