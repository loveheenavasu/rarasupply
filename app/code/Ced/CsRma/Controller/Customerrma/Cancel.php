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

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;

/**
 * Class Cancel
 * @package Ced\CsRma\Controller\Customerrma
 */
class Cancel extends \Ced\CsRma\Controller\Link
{
    /**
     * @var \Ced\CsRma\Model\RequestFactory
     */
    protected $requestFactory;

    /**
     * Cancel constructor.
     * @param \Ced\CsRma\Model\RequestFactory $requestFactory
     * @param \Ced\CsRma\Helper\Config $configHelper
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
     */
    public function __construct(
        \Ced\CsRma\Model\RequestFactory $requestFactory,
        \Ced\CsRma\Helper\Config $configHelper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
    )
    {
        $this->requestFactory = $requestFactory;
        parent::__construct($configHelper, $context, $customerSession, $resultRedirectFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $response = new DataObject();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $id = $this->getRequest()->getParam('rma_request_id');
        try {
            $model = $this->requestFactory->create();
            $model->load($id)->setData('status', 'Cancelled')->save();

            $this->messageManager->addSuccessMessage(__('The request status has been changed.'));
            $response->setError(0);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $response->setError(1);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong  with this request.'));
            $response->setError(1);
        }
        $resultJson->setData($response->toArray());
        return $resultJson;
    }

}