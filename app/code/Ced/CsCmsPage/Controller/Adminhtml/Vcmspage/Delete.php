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
 * Class Delete
 * @package Ced\CsCmsPage\Controller\Adminhtml\Vcmspage
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsCmsPage\Model\CmspageFactory
     */
    protected $cmspageFactory;

    /**
     * Delete constructor.
     * @param \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory
     * @param Action\Context $context
     */
    public function __construct(
        \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory,
        Action\Context $context
    )
    {
        parent::__construct($context);

        $this->cmspageFactory = $cmspageFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Cms::page_delete');
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('page_id');
        if (!is_array($id)) {
            $ids[] = $id;
        } else {
            $ids = $id;
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($ids) {
            $title = "";
            try {
                foreach ($ids as $id) {
                    $model = $this->cmspageFactory->create();
                    $model->load($id);
                    $title = $model->getTitle();
                    $model->delete();
                    // display success message

                    // go to grid
                    $this->_eventManager->dispatch(
                        'adminhtml_cmspage_on_delete',
                        ['title' => $title, 'status' => 'success']
                    );
                }
                $this->messageManager->addSuccessMessage(__('The page has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_cmspage_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['page_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a page to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
