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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Ced\CsRma\Controller\Adminhtml\Status
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsRma\Model\RmastatusFactory
     */
    protected $rmastatusFactory;

    /**
     * MassDelete constructor.
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
        $data = $this->getRequest()->getParams();
        if (!is_array($data['selected'])) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                foreach ($data['selected'] as $key) {
                    $this->rmastatusFactory->create()
                        ->load($key)
                        ->delete();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($data['selected']))
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while deleting these records.'));
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('rma/*/');
        return $resultRedirect;
    }

}  

