<?php

namespace Ced\Advertisement\Controller\Adminhtml\Blocks;

class MassStatus extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\Advertisement\Model\Blocks $blocks,
        \Ced\Advertisement\Model\Purchased $purchased,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->purchased = $purchased;
        $this->scopeConfig = $scopeConfig;      
        $this->_blocks = $blocks;
    }
        

    /**
     * Promo quote edit action
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws Exception
     */
    public function execute()
    {
        $inline = $this->getRequest()->getParam('inline', 0);
        $ids = $this->getRequest()->getParam('id');
        $status = $this->getRequest()->getParam('status', '');
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $block_table = $resource->getTableName('ced_advertisement_block');

        if($inline){
            $ids = [$ids];
        }
        try{
            if (!is_array($ids)) {
                 $this->messageManager->addErrorMessage($this->__('Please select block(s)'));
            } else {
                $count = count($ids);
                $ids = implode(',', $ids);
                $query = "Update `".$block_table."` Set `status` = '".$status."' where `id` IN (".$ids.")";
                $connection->query($query);                

                $update_purchased_ads_blocks = $this->scopeConfig->getValue('advertisement/ads_settings/update_purchased_ads_blocks', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);         
                if($update_purchased_ads_blocks && ($status==1)){
                    $purchased_table = $resource->getTableName('ced_advertisement_purchased_ads');
                    $blocksColl = $this->_blocks->getCollection()->addFieldToFilter('id',['in' => $ids]);
                    foreach ($blocksColl as $blocks) {
                        $query1 = "Update `".$purchased_table."` Set `block_title` = '".$blocks->getTitle()."', `block_url` = '".$blocks->getUrl()."', `block_image` = '".$blocks->getImage()."' where `block_id` = '".$blocks->getId()."'";
                        $connection->query($query1);
                    }
                }  
                $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been updated.', $count));
            }                     
        }catch (\Exception $e) {         
            $this->messageManager->addErrorMessage(__(' An error occurred while updating the block(s) status.'));
        }
        $this->_redirect('*/*/index');
    }    
}
