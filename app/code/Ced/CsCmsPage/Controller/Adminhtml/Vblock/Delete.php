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
 * Class Delete
 * @package Ced\CsCmsPage\Controller\Adminhtml\Vblock
 */
class Delete extends \Magento\Cms\Controller\Adminhtml\Block
{

    /**
     * @var \Ced\CsCmsPage\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * Delete constructor.
     * @param \Ced\CsCmsPage\Model\BlockFactory $blockFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Ced\CsCmsPage\Model\BlockFactory $blockFactory,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        parent::__construct($context, $coreRegistry);

        $this->blockFactory = $blockFactory;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('block_id');
        if (!is_array($id)) {
            $ids[] = $id;
        } else {
            $ids = $id;
        }
        if ($ids) {
            try {
                foreach ($ids as $id) {
                    $model = $this->blockFactory->create();
                    $model->load($id);
                    $model->delete();
                    // display success message
                }
                $this->messageManager->addSuccessMessage(__('You deleted the block.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['block_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a block to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
