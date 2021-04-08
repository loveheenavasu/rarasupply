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

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 * @package Ced\CsRma\Controller\Adminhtml\Status
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\Rma\Model\RmastatusFactory
     */
    protected $rmaStatusFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Save constructor.
     * @param \Ced\CsRma\Model\RmastatusFactory $rmaStatusFactory
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
     */
    public function __construct(
        \Ced\CsRma\Model\RmastatusFactory $rmaStatusFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
    )
    {
        $this->rmaStatusFactory = $rmaStatusFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

    /**
     * @var execute
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $model = $this->rmaStatusFactory->create();
        if ($data) {
            if ($id = $this->getRequest()->getParam('status_id')) {
                $model->load($id);
            }
            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Updated Successfully'));
                $this->backendSession->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getStatusId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                echo $e->getMessage();
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('status_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}



