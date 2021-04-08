<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Advertisement\Controller\Blocks;

use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Framework\App\Action\Action
{
    protected $_fileUploaderFactory;  
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
         \Magento\Framework\App\Action\Context $context,
         \Ced\Advertisement\Model\Blocks $blocks,
         \Magento\Customer\Model\Session $custSession,
         \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
         \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        parent::__construct($context);
        $this->_adBlock = $blocks;
        $this->scopeConfig = $scopeConfig;
        $this->_custSession = $custSession;
        $this->_fileUploaderFactory = $fileUploaderFactory;  
    }
    /**
     * Managing newsletter subscription page
     *
     * @return void
     */
    public function execute()
    {     
        try {  
            $resultRedirect = $this->resultRedirectFactory->create();        
            if(!$this->_custSession->isLoggedIn() ){
                $this->messageManager->addError(__('You are not allowed to perform this action.')); 
                $resultRedirect->setPath('/');
                return $resultRedirect;
            } 
            $data = $this->getRequest()->getParams();
            $file_name = '';
            if(isset($_FILES['image']['name']) && $_FILES['image']['tmp_name']){
                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'image']); 
                $uploader->setAllowedExtensions(['gif', 'jpg', 'jpeg', 'png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                
                $path = $this->_objectManager->get('Magento\Framework\Filesystem')
                        ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('advertisement/');
                $result = $uploader->save($path);
                $file_name = $result['file'];
            }
            $approval_blocks = $this->scopeConfig->getValue('advertisement/ads_settings/approval_blocks', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($data && isset($data)) {
                $model = $this->_adBlock;
                if(isset($data['id']) && $data['id']){
                    $model = $model->load($data['id']);
                    $model->setData('status', 1);                    
                    if($approval_blocks){
                        $model->setData('status', 0);
                    }
                    
                }else{
                    $model->setData('status', 2);
                    if(!$approval_blocks){
                        $model->setData('status', 1);
                    }
                    $model->setData('customer_id', $this->_custSession->getId());
                }
                $model->setData('title', $data['title']);       
                $model->setData('url', $data['url']);    
                if($file_name){
                    $model->setData('image', $file_name);           
                }                   
                $model->save();  
                $this->messageManager->addSuccessMessage(__('Advertisement Block has been created.'));
                $resultRedirect->setPath('advertisement/blocks/edit',['id' => $model->getId(), '_current' => true]);
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Some error occurred while saving data.'));
            $resultRedirect->setPath('advertisement/blocks/index');
        } 
        return $resultRedirect;  
    }
}
