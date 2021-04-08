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

namespace Ced\CsRma\Controller\Adminhtml\AllRma;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Delete extends \Ced\CsRma\Controller\Adminhtml\AllRma
{
    protected $requestFactory;

    public function __construct(
        \Ced\CsRma\Model\RequestFactory $requestFactory,
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        $this->requestFactory = $requestFactory;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $id = $this->getRequest()->getParam('id');
        try {
            $model = $this->requestFactory->create()
                ->setId($id)->delete();
            $this->messageManager->addSuccessMessage(__('The request has been deleted.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong  deleting this request.'));
        }
        return $resultRedirect->setPath('csrma/*/');
    }

}