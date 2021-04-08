<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Advertisement\Controller\Blocks;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Framework\App\Action\Action
{

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ced\Advertisement\Model\Blocks $blocks,
        \Magento\Customer\Model\Session $custSession,
        \Ced\Advertisement\Helper\Data $adHelper,
        \Magento\Framework\Registry $registry,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_adBlock = $blocks;
        $this->adHelper = $adHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_custSession = $custSession;
        $this->_coreRegistry = $registry;
    }
    /**
     * Managing newsletter subscription page
     *
     * @return void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();        
        if(!$this->_custSession->isLoggedIn() || !$this->adHelper->isModuleEnable()){
            $this->messageManager->addError(__('You are not allowed to perform this action.')); 
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }
        $this->_view->loadLayout();

        if ($block = $this->_view->getLayout()->getBlock('advertisement_block_edit')) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        $resultPage = $this->resultPageFactory->create(); 
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('advertisement/blocks/edit');
        } 

        if($id = $this->getRequest()->getParam('id')) {
            $data = $this->_adBlock->load($id);
            $this->_coreRegistry->register('advertisementBlock',$data);
            $this->_view->getPage()->getConfig()->getTitle()->set(__('Edit Advertisement Block'));
        }else{
            $this->_view->getPage()->getConfig()->getTitle()->set(__('New Advertisement Block'));
        }        
        $this->_view->renderLayout();
    }
}
