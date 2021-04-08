<?php

namespace Ced\Advertisement\Controller\Adminhtml\Plan;

class Save extends \Magento\Backend\App\Action
{
	public function execute() {
		$resultRedirect = $this->resultRedirectFactory->create();
		$this->scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
		$this->_storeRepository = $this->_objectManager->create('Magento\Store\Model\StoreRepository');
	 	$data = $this->getRequest()->getPost();	 
	 	if(isset($data)){
	 		try{
	 			$productTypeId = 'virtual';
			 	$product = $this->_objectManager->create('Magento\Catalog\Model\Product');

		        $productAttributeSetId = $product->getDefaultAttributeSetId();
			 	if(isset($data['id'])) {
			 		$product = $product->load($data['id']);	 		
			 	} else {
			 		$stores = $this->_storeRepository->getList();
			        $websiteIds = [];
			        foreach ($stores as $store) {
			            $websiteId = $store["website_id"];
			            array_push($websiteIds, $websiteId);
			        }
			 		
			 		$sku = $data['name'];
			 		$proColl = $this->_objectManager->create('Magento\Catalog\Model\Product')
			 								->getCollection()
			 								->addFieldToFilter('sku', ['like' => '%'.$sku.'%']);
					if($count = count($proColl)){
						$count++;
						$sku = $sku.'_'.$count;
					}
			 		$product->setData('sku',$sku);
			 		$product->setAttributeSetId($productAttributeSetId);
			 		$product->setVisibility(1);
			 		$product->setWebsiteIds($websiteIds);
			 		$product->setTypeId($productTypeId);
			 		$product->setData('is_plan',1);
			 	}
			 	$qty = $data['qty'];
		 		$product->setQty($qty);
		 		$product->setStockData(
	                    array(
	                        'use_config_manage_stock' => 0,
	                        'manage_stock' => 1,
	                        'is_in_stock' => 1,
	                        'qty' => $qty
	                    )
	                );	 	
			 	$product->setData('status',$data['status']);
			 	$product->setData('name',$data['name']);
			 	$product->setData('price',$data['price']);
			 	$product->setData('duration',$data['duration']);
			 	$product->setData('position_identifier',$data['position_identifier']); 	 
			 	$product->save();

			 	$this->messageManager->addSuccessMessage(__('You have created the Plan.'));

			 	if($this->getRequest()->getParam('back') == 'edit')
			 		$resultRedirect->setPath('advertisement/plan/edit', ['id' => $product->getId()]);
			 	else
			 		$resultRedirect->setPath('advertisement/plan/index');
				
	 		}catch(\Exception $e){
	 			$this->messageManager->addErrorMessage(__('Some error occurred while saving Plan.'));
       			$resultRedirect->setPath('advertisement/plan/index');
	 		}	 		
	 	}else{
	 		$this->messageManager->addErrorMessage(__('Some error occurred while saving Plan.'));
       		$resultRedirect->setPath('advertisement/plan/index');
	 	}
	 	
		return $resultRedirect;
	 }
}

