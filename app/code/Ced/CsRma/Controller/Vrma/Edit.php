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
 * Class Edit
 * @package Ced\CsRma\Controller\Vrma
 */
class Edit extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsRma\Model\RequestFactory
     */
    protected $requestFactory;

    /**
     * Edit constructor.
     * @param \Ced\CsRma\Model\RequestFactory $requestFactory
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

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        if (!$this->_getSession()->getVendorId()) {
            return;
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('rma_id');
        $vendorRma = $this->requestFactory->create()->load($id);
        if ($this->_getSession()->getVendorId() != $vendorRma->getVendorId()) {
            $this->messageManager->addErrorMessage(__('There is no such RMA request initiated.'));
            return $resultRedirect->setPath('csrma/vrma/index');
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Return Request'));
        $resultPage->getConfig()->getTitle()->prepend(__('#' . $vendorRma->getRmaId()));
        return $resultPage;
    }
}
