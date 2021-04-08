<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Advertisement\Controller\Adminhtml\Purchased;
class MassStatus extends \Magento\Backend\App\Action
{
    public function __construct(
                                \Magento\Backend\App\Action\Context $context,
                                \Ced\Advertisement\Model\ResourceModel\Purchased\CollectionFactory $purchasedFactory
                            ){
        $this->purchasedFactory = $purchasedFactory;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $ids = $this->getRequest()->getParam('id');
        $i = 0;
        if($ids!=''){
            $idsArr = explode(",",$ids);
            $purchasedCollection  = $this->purchasedFactory->create()
                                        ->addFieldToSelect('*')
                                        ->addFieldToFilter('id',['in'=>$idsArr]);
                                        
            foreach($purchasedCollection as $key =>$object){
                $collection = $this->purchasedFactory->create()
                            ->addFieldToSelect('*')
                            ->addFieldToFilter('id',$object->getId())
                            ->getLastItem();
                            
                $collection->setStatus($this->getRequest()->getParam('status'));
                $collection->save();
                $i++;
            }
        }
        
        if($i >0)
            $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been updated.', $i));   
        $this->_redirect('advertisement/purchased/index');
    }
}
