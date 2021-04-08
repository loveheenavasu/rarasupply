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

namespace Ced\CsRma\Controller\Customerrma;

/**
 * Class Cancelorder
 * @package Ced\CsRma\Controller\Customerrma
 */
class Cancelorder extends \Ced\CsRma\Controller\Link
{
    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * Cancelorder constructor.
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Ced\CsRma\Helper\Config $configHelper
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
     */
    public function __construct(
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Ced\CsRma\Helper\Config $configHelper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
    )
    {
        $this->orderManagement = $orderManagement;
        parent::__construct($configHelper, $context, $customerSession, $resultRedirectFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {

        $id = $this->getRequest()->getParam('order_id');

        try {
            $this->orderManagement->cancel($id);
            $this->messageManager->addSuccessMessage('Orders Cancelled  Successfully...');

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong  with this request.'));

        }
        return $this->_redirect('sales/order/view', ['order_id' => $id]);

    }

}