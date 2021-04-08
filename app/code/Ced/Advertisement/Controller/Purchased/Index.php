<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Advertisement\Controller\Purchased;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\Advertisement\Helper\Data $adHelper,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->adHelper = $adHelper;
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Managing newsletter subscription page
     *
     * @return void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create(); 
        $resultPage = $this->resultPageFactory->create(); 
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('advertisement/purchased/index');
        }        
        if(!$this->_customerSession->isLoggedIn() || !$this->adHelper->isModuleEnable()){
            $this->messageManager->addError(__('You are not allowed to perform this action.')); 
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }   
        $this->_view->loadLayout();

        if ($block = $this->_view->getLayout()->getBlock('advertisement_purchased')) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Purchased Plans'));
        $this->_view->renderLayout();
    }
}
