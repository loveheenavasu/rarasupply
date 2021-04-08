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

namespace Ced\CsCmsPage\Controller\Wysiwyg\Images;

/**
 * Class Index
 * @package Ced\CsCmsPage\Controller\Wysiwyg\Images
 */
class Index extends \Ced\CsCmsPage\Controller\Wysiwyg\Images
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Ced\CsCmsPage\Helper\Wysiwyg\Images
     */
    protected $images;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Cms\Model\Wysiwyg\Images\Storage $storage
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Ced\CsCmsPage\Helper\Wysiwyg\Images $images
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Wysiwyg\Images\Storage $storage,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Ced\CsCmsPage\Helper\Wysiwyg\Images $images
    )
    {
        parent::__construct($context, $coreRegistry, $storage);

        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->images = $images;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        try {
            $this->images->getCurrentPath();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->_initAction();
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->addHandle('overlay_popup');
        $block = $resultLayout->getLayout()->getBlock('wysiwyg_images.js');
        if ($block) {
            $block->setStoreId($storeId);
        }
        return $resultLayout;
    }
}
