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

namespace Ced\CsCmsPage\Controller\Adminhtml\Vblock;

/**
 * Class Save
 * @package Ced\CsCmsPage\Controller\Adminhtml\Vblock
 */
class Save extends \Magento\Cms\Controller\Adminhtml\Block
{
    /**
     * @var \Ced\CsCmsPage\Helper\Data
     */
    protected $cmsHelper;

    /**
     * @var \Ced\CsCmsPage\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Ced\CsCmsPage\Model\VendorblockFactory
     */
    protected $vendorblockFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Save constructor.
     * @param \Ced\CsCmsPage\Helper\Data $cmsHelper
     * @param \Ced\CsCmsPage\Model\BlockFactory $blockFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsCmsPage\Model\VendorblockFactory $vendorblockFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Ced\CsCmsPage\Helper\Data $cmsHelper,
        \Ced\CsCmsPage\Model\BlockFactory $blockFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsCmsPage\Model\VendorblockFactory $vendorblockFactory,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        parent::__construct($context, $coreRegistry);

        $this->cmsHelper = $cmsHelper;
        $this->blockFactory = $blockFactory;
        $this->vendorFactory = $vendorFactory;
        $this->vendorblockFactory = $vendorblockFactory;
        $this->backendSession = $context->getSession();
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->cmsHelper->isEnabled()) {
            return;
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($this->getRequest()->getPost() && $this->getRequest()->getParam('block_id') && $this->getRequest()->getParam('block_id') > 0) {
            $BlockId = $this->getRequest()->getParam('block_id');

            $title = $this->getRequest()->getPost('title');
            $identifier = $this->getRequest()->getPost('identifier');

            $store = $this->getRequest()->getPost('stores');
            $is_active = $this->getRequest()->getPost('is_active');
            $content = $this->getRequest()->getPost('content');
            $date = date("Y-m-d H:i:s");
            $Block = $this->blockFactory->create()->load($BlockId);
            try {
                if (null !== $Block->getId()) {
                    $VendorId = $Block->getVendorId();

                    if ($VendorId > 0) {
                        $vendorCollection = $this->vendorFactory->create()->load($VendorId);

                        $identifier = 'vendor-shop/' . $vendorCollection->getShopUrl() . '/' . $identifier;

                        $Block->setTitle($title);
                        $Block->setIdentifier($identifier);
                        $Block->setIsActive($is_active);
                        $Block->setContent($content);
                        $Block->setUpdateTime($date);
                        $Block->save();

                        if ($Block->getBlockId() != null && $Block->getBlockId() > 0) {

                            $VendorBlockStore = $this->vendorblockFactory->create()->getCollection()
                                ->addFieldToFilter('vendor_id', $VendorId)
                                ->addFieldToFilter('block_id', $BlockId);
                            if (sizeof($VendorBlockStore) > 0) {
                                foreach ($VendorBlockStore as $block) {
                                    $VendorBlock = $this->vendorblockFactory->create()->load($block->getId());
                                    $VendorBlock->delete();
                                }
                            }
                            if (isset($store) && sizeof($store) > 0) {
                                foreach ($store as $storeId) {
                                    $Vblockstore = $this->vendorblockFactory->create();
                                    $Vblockstore->setBlockId($Block->getBlockId());
                                    $Vblockstore->setStoreId($storeId);
                                    $Vblockstore->setVendorId($VendorId);
                                    $Vblockstore->save();
                                }
                                $this->messageManager->addSuccessMessage(__('You saved this page.'));
                                $this->backendSession->setFormData(false);
                                if ($this->getRequest()->getParam('back')) {
                                    return $resultRedirect->setPath('*/*/edit', ['block_id' => $Block->getId(), '_current' => true]);
                                }
                                if ($this->getRequest()->getParam('back')) {
                                    return $this->_redirect('*/*/edit', array('block_id' => $BlockId, '_current' => true));
                                }

                                return $this->_redirect('*/*/index');

                            }
                        }
                    }
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
            }

            $this->_getSession()->setFormData($data);
        }
        $this->_redirect('*/*/index');
    }
}
