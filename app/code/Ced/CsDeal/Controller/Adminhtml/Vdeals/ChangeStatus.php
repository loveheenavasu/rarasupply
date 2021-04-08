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
 * @package     Ced_CsDeal
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Controller\Adminhtml\Vdeals;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class ChangeStatus
 * @package Ced\CsDeal\Controller\Adminhtml\Vdeals
 */
class ChangeStatus extends \Magento\Backend\App\Action
{

    /**
     * ChangeStatus constructor.
     * @param \Ced\CsDeal\Model\DealFactory $dealFactory
     * @param Context $context
     */
    public function __construct(
        \Ced\CsDeal\Model\DealFactory $dealFactory,
        Context $context
    )
    {
        $this->dealFactory = $dealFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $checkstatus = $this->getRequest()->getParam('status');
        $dealId = $this->getRequest()->getParam('deal_id');
        if ($dealId > 0 && $checkstatus != '') {
            try {
                $errors = $this->dealFactory->create()->changeVdealStatus($dealId, $checkstatus);

                if ($errors['success'])
                    $this->messageManager->addSuccessMessage(__("Status changed Successfully"));
                else if ($errors['error'])
                    $this->messageManager->addErrorMessage(__("Can't process approval/disapproval for the Product.The Product's vendor is disapproved or not exist."));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('%s', $e->getMessage()));
            }
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}