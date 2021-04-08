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

namespace Ced\CsRma\Controller\Vrma;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Save
 * @package Ced\CsRma\Controller\Vrma
 */
class Save extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsRma\Model\RequestFactory
     */
    protected $requestFactory;

    /**
     * @var \Ced\CsRma\Helper\OrderDetail
     */
    protected $detailHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Save constructor.
     * @param \Ced\CsRma\Model\RequestFactory $requestFactory
     * @param \Ced\CsRma\Helper\OrderDetail $detailHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Ced\CsRma\Model\RequestFactory $requestFactory,
        \Ced\CsRma\Helper\OrderDetail $detailHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
        $this->requestFactory = $requestFactory;
        $this->detailHelper = $detailHelper;
        $this->dateTime = $dateTime;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /*
    * Get the value of customer session
    */
    /**
     * @return Session
     */
    public function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();

        $resultRedirect = $this->resultRedirectFactory->create();
        $model = $this->requestFactory->create();
        $rmaOrderHelper = $this->detailHelper;

        $dateTime = $this->dateTime;
        if ($data) {
            if ($id = $this->getRequest()->getParam('rma_request_id')) {
                $model->load($id);
            }
            $allowCompleted = true;
            if ($model->getStatus() == 'Pending') {
                $allowCompleted = false;
            }

            if ($model->getStatus() != "Approved" && $data['status'] != 'Cancelled' && $model->getStatus() != "Cancelled" && $model->getStatus() != "Completed") {
                if (($data['status'] == 'Approved' || $allowCompleted) && ($data['resolution_requested'] == 'Refund' || $data['resolution_requested'] == 'Cancel')) {

                    $creditemo = $rmaOrderHelper->generateCreditMemoForRma($id, $data);

                    if (isset($creditemo['error']) && $creditemo['error']) {
                        $this->messageManager->addErrorMessage(__('Cant Save Rma Request'));
                        return $resultRedirect->setPath('*/*/');
                    }
                }
            }
            $model->setData('updated_at', $dateTime->gmtDate());

            try {
                $model->setData('status', $data['status']);
                $model->save();
                $this->session->setFormData(false);
                $resultRedirect = $this->resultRedirectFactory->create();
                $this->messageManager->addSuccessMessage(__('Updated Successfully'));
                return $resultRedirect->setPath('*/*/');

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));

            }
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('rma_request_id')]);
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
