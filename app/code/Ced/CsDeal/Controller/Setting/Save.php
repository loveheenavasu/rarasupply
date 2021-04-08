<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @category    Ced
 * @package     Ced_CsDeal
 * @author        CedCommerce Core Team <coreteam@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Ced\CsDeal\Controller\Setting;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Save
 * @package Ced\CsDeal\Controller\Setting
 */
class Save extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsDeal\Model\DealsettingFactory
     */
    protected $dealsettingFactory;

    /**
     * Save constructor.
     * @param \Ced\CsDeal\Model\DealsettingFactory $dealsettingFactory
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
        \Ced\CsDeal\Model\DealsettingFactory $dealsettingFactory,
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
        $this->dealsettingFactory = $dealsettingFactory;
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $post_data = $this->getRequest()->getPost();
            $vendor_id = $post_data['vendor_id'];
            $status = $post_data['status'];
            $deal_list = $post_data['deal_list'];
            $timer_list = $post_data['timer_list'];
            $deal_message = $post_data['deal_message'];
            $store = $this->csmarketplaceHelper->getStore()->getId();
            $setting_id = $post_data['setting_id'];
            unset($post_data['setting_id']);
            if ($setting_id) {
                $model = $this->dealsettingFactory->create()->load($setting_id);
            } else {
                $model = $this->dealsettingFactory->create();
            }
            if ($deal_message == '') {
                $this->messageManager->addErrorMessage(__('Deal message can not be empty'));
                return $this->_redirect('*/*/');
            }
            $model->setData('vendor_id', $vendor_id)
                ->setData('status', $status)
                ->setData('deal_list', $deal_list)
                ->setData('timer_list', $timer_list)
                ->setData('deal_message', $deal_message)
                ->setData('store', $store);
            $model->save();
            $this->messageManager->addSuccessMessage(__('Setting saved Successfully.'));
            $this->_redirect('*/*/');
        } else {
            $this->_redirect('*/*/');
        }
    }

}
