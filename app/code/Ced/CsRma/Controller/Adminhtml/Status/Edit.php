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
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRma\Controller\Adminhtml\Status;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Ced\CsRma\Controller\Adminhtml\Status
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Ced\CsRma\Model\RmastatusFactory
     */
    protected $rmastatusFactory;

    /**
     * Edit constructor.
     * @param \Ced\CsRma\Model\RmastatusFactory $rmastatusFactory
     * @param \Magento\Backend\Model\Session $backendSession
     * @param Action\Context $context
     * @param Registry $registry
     */
    public function __construct(
        \Ced\CsRma\Model\RmastatusFactory $rmastatusFactory,
        \Magento\Backend\Model\Session $backendSession,
        Action\Context $context,
        Registry $registry
    )
    {
        $this->_coreRegistry = $registry;
        $this->rmastatusFactory = $rmastatusFactory;
        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */

        $resultPage = $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Ced_CsRma:: Manage RMA Status')
            ->addBreadcrumb(__('RMA'), __('RMA'))
            ->addBreadcrumb(__('Manage Status'), __('Manage Status'));
        return $resultPage;
    }

    /**
     * Edit status
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->rmastatusFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This page no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }


        $this->_coreRegistry->register('ced_csrma_status', $model);

        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Status') : __('New Status'),
            $id ? __('Edit Status') : __('New Status')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Status'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getStatus() : __('New Status'));

        $resultPage->addContent($resultPage->getLayout()->createBlock('Ced\CsRma\Block\Adminhtml\Status\Edit'));
        return $resultPage;
    }
}
