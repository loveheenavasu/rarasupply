<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Advertisement\Controller\Blocks;

use Magento\Framework\App\Filesystem\DirectoryList;

class Delete extends \Magento\Framework\App\Action\Action
{
    protected $_fileUploaderFactory;  
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $custSession,
        \Ced\Advertisement\Model\Blocks $blocks,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        parent::__construct($context);
        $this->_adBlock = $blocks;
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
            $id = $this->getRequest()->getParam('id');
            
            if($id){
                $model = $this->_adBlock->load($id);
                $image_name = $model->getImage();
                $model->delete();
                if($image_name){
                    $path = $this->_objectManager->get('Magento\Framework\Filesystem')
                        ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('advertisement/');
                    $path = $path.$image_name;
                    if(file_exists($path)){
                        unlink($path);
                    }
                }
            }
            
            $this->messageManager->addSuccess(__('Advertisement Block has been deleted.'));                
            $resultRedirect->setPath('advertisement/blocks/index');
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Some error occurred while deleting data.'));                
            $resultRedirect->setPath('advertisement/blocks/index');
        } 
        return $resultRedirect;  
    }
}
