<?php

namespace Ced\Advertisement\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class OrderSaveAfter implements ObserverInterface
{   
    protected $_request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\DB\Transaction $transaction

    ) { 
        $this->_request = $request;
        $this->_transaction = $transaction;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        try{
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Your text message');
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $observer->getEvent()->getOrder();
            $items = $order->getAllItems();
            $order_id = $order->getIncrementId();
            $customer_id = $order->getCustomerId();
            if($order_id){                
                $flag = false;
                foreach ($items as $item) {
                    $product = $item->getProduct();
                    $proId = $product->getId();
                    $duration = $product->getDuration();
                    if($product->getIsPlan()){    
                        $block_id = $this->_objectManager->create('Magento\Quote\Model\Quote\Item')->load($item->getQuoteItemId())->getBlockId(); 
                        if($block_id){
                            $logger->info('Your text message1'.$item->getItemId());
                            $logger->info('Your text message2'.$item->getQtyOrdered());
                            $flag = true;
                            $blockData = $this->_objectManager->create('Ced\Advertisement\Model\Blocks')->load($block_id);
                            $purchased = $this->_objectManager->create('Ced\Advertisement\Model\Purchased');
                            $purchased->setCustomerId($customer_id);
                            $purchased->setDuration($duration);
                            $purchased->setOrderId($order_id);
                            $purchased->setPlanId($proId);
                            $purchased->setPrice($product->getPrice());
                            $purchased->setPlanTitle($product->getName());
                            $purchased->setPositionIdentifier($product->getPositionIdentifier());
                            $purchased->setBlockId($block_id);
                            $purchased->setBlockImage($blockData->getImage());
                            $purchased->setBlockTitle($blockData->getTitle());
                            $purchased->setStatus(1);
                            $purchased->setBlockUrl($blockData->getUrl());
                            $purchased->save();
                        }   
                    }   
                }
                if($flag){
                    $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
                    $connection = $resource->getConnection();
                    $sales_order_table = $resource->getTableName('sales_order');
                    $sales_grid_table = $resource->getTableName('sales_order_grid');
                    $sql_sales_order = "Update " . $sales_order_table . " Set `is_plan` = '1' where `increment_id` = '".$order_id."'";
                    $connection->query($sql_sales_order);
                    $sql_sales_grid = "Update " . $sales_grid_table . " Set `is_plan` = '1' where `increment_id` = '".$order_id."'";
                    $connection->query($sql_sales_grid);

                    /*create invoice*/
                    if(!$order->canInvoice())
                    {
                        throw new \Magento\Framework\Exception\LocalizedException(__('Cannot create invoice.'));
                    }
                    $invoice = $this->_objectManager->create('Magento\Sales\Api\InvoiceOrderInterface')->execute($order_id, $capture = true,);

                   
                }
            }            
        }catch(\Exception $e) {
            echo $e->getMessage();die;
        }        
    }

}
