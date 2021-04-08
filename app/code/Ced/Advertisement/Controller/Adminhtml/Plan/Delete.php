<?php

namespace Ced\Advertisement\Controller\Adminhtml\Plan;

class Delete extends \Magento\Backend\App\Action
{
	public function execute() {

		$resultRedirect = $this->resultRedirectFactory->create();		
	 	$plan_id = $this->getRequest()->getParam('id');	 
	 	if(isset($plan_id)){
	 		try{	 			
			 	$product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($plan_id);	       
			 	$product->delete();
			 	$this->messageManager->addSuccessMessage(__('You have deleted the plan.'));
				$resultRedirect->setPath('advertisement/plan/index');
	 		}catch(\Exception $e){
	 			$this->messageManager->addErroMessage(__('Some error occurred while deleting Plan.'));
       			$resultRedirect->setPath('advertisement/plan/index');
	 		}	 		
	 	}else{
	 		$this->messageManager->addErroMessage(__('Some error occurred while deleting Plan.'));
       		$resultRedirect->setPath('advertisement/plan/index');
	 	}
	 	
		return $resultRedirect;
	 }
}

