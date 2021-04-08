<?php

namespace Ced\Advertisement\Controller\Adminhtml\Position;

class MassStatus extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\Advertisement\Model\Positions $position
    ) {
        parent::__construct($context);
        $this->_positions = $position;
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
        $position_table = $resource->getTableName('ced_advertisement_positions');

        if($inline){
            $ids = [$ids];
        }
        try{
            if (!is_array($ids)) {
                 $this->messageManager->addErrorMessage($this->__('Please select position(s)'));
            } else {
                $count = count($ids);
                $ids = implode(',', $ids);
                $query = "Update `".$position_table."` Set `position_status` = '".$status."' where `id` IN (".$ids.")";
                $connection->query($query);

                /*Disable status of plans whose positions are being disappoved */
                if(!$status){
                    $positions_identifier = $this->_objectManager->create('Ced\Advertisement\Model\Positions')
                                            ->getCollection()
                                            ->addFieldToFilter('id', ['in' => $ids])
                                            ->getColumnValues('identifier');

                    $positions_identifier = implode(',',  $positions_identifier);
                    $product_ids = $this->_objectManager->create('Magento\Catalog\Model\Product')
                                        ->getCollection()
                                        ->addAttributeToFilter('position_identifier', ['in' => $positions_identifier])
                                        ->getAllIds();

                    $this->_objectManager->get(\Magento\Catalog\Model\Product\Action::class)
                    ->updateAttributes($product_ids, ['status' => 2], 0);
                }
                $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been updated.', $count));
            }            
        }catch (\Exception $e) {
            echo $e->getMessage();die;
            $this->messageManager->addErrorMessage(__(' An error occurred while updating the position(s) status.'));
        }
        $this->_redirect('*/*/index');
    }    
}
