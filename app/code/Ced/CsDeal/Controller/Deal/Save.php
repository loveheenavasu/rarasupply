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

namespace Ced\CsDeal\Controller\Deal;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Save
 * @package Ced\CsDeal\Controller\Deal
 */
class Save extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsDeal\Helper\Data
     */
    protected $csdealHelper;

    /**
     * @var \Ced\CsDeal\Model\DealFactory
     */
    protected $dealFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Save constructor.
     * @param \Ced\CsDeal\Helper\Data $csdealHelper
     * @param \Ced\CsDeal\Model\DealFactory $dealFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
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
        \Ced\CsDeal\Helper\Data $csdealHelper,
        \Ced\CsDeal\Model\DealFactory $dealFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
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
        $this->csdealHelper = $csdealHelper;
        $this->dealFactory = $dealFactory;
        $this->productFactory = $productFactory;
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
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $post_data = $this->getRequest()->getPost();
            if ((strtotime($post_data['end_date']) < strtotime($post_data['start_date'])) && $id = $this->getRequest()->getParam('deal_id')) {
                $this->messageManager->addErrorMessage(__('End date must be greater or equal to start date.'));
                $this->_redirect('csdeal/deal/edit', ['deal_id' => $id]);
                return;
            }

            $needApproval = $this->csdealHelper->isApprovalNeeded();
            if ($needApproval && !$this->getRequest()->getParam('deal_id'))
                $post_data['admin_status'] = '2';
            elseif (!$this->getRequest()->getParam('deal_id'))
                $post_data['admin_status'] = '1';
            $model = $this->dealFactory->create();
            $name = $this->productFactory->create()->load($post_data['product_id'])->getName();

            $model->setData('product_id', $post_data['product_id'])
                ->setData('product_name', $name)
                ->setData('start_date', $post_data['start_date'])
                ->setData('end_date', $post_data['end_date'])
                ->setData('vendor_id', $post_data['vendor_id']);

            if (!$this->getRequest()->getParam('deal_id'))
                $model->setData('admin_status', $post_data['admin_status']);

            $model->setData('status', $post_data['status'])
                ->setData('deal_price', $post_data['deal_price'])
                ->setDealId($this->getRequest()->getParam('deal_id'))->save();
            if ($post_data['admin_status'] == '1' || $this->getRequest()->getParam('deal_id')) {
                $this->_eventManager->dispatch('controller_action_predispatch_ced_csdeal_create', array('deal' => $model,));
            }
            $this->messageManager->addSuccessMessage(__('Deal created Successfully.'));
            $this->_redirect('*/*/listi');
            return;
        } else {
            $this->_redirect('*/*/create');
        }
    }
}
