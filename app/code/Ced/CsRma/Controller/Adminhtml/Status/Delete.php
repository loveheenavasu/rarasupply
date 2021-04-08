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

/**
 * Class Delete
 * @package Ced\CsRma\Controller\Adminhtml\Status
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsRma\Model\RmastatusFactory
     */
    protected $rmastatusFactory;

    /**
     * Delete constructor.
     * @param \Ced\CsRma\Model\RmastatusFactory $rmastatusFactory
     * @param Action\Context $context
     */
    public function __construct(\Ced\CsRma\Model\RmastatusFactory $rmastatusFactory, Action\Context $context)
    {
        $this->rmastatusFactory = $rmastatusFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $id = $this->getRequest()->getParam('id');
        try {
            $model = $this->rmastatusFactory->create()
                ->setId($id)->delete();
            $this->messageManager->addSuccessMessage(__('The status has been deleted.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong  deleting this status.'));
        }
        return $resultRedirect->setPath('csrma/*/');
    }

}