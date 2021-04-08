<?php

namespace Ced\Advertisement\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class OrderPlaceAfter implements ObserverInterface
{   
    protected $_request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) { 
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        try{
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->_checkoutSession = $this->_objectManager->get('Magento\Checkout\Model\Session');
            $plan_data = $this->_checkoutSession->getPlanData();
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
                   // echo  $duration.'==';
                    if($product->getIsPlan()){
                        $flag = true;
                        if(isset($plan_data[$proId])){
                            $block_id = $plan_data[$proId];
                            $blockData = $this->_objectManager->create('Ced\Advertisement\Model\Blocks')->load($block_id);
                            $purchased = $this->_objectManager->create('Ced\Advertisement\Model\Purchased');
                            $purchased->setCustomerId($customer_id);
                            $purchased->setDuration($duration);
                            $purchased->setOrderId($order_id);
                            $purchased->setPlanId($proId);
                            $purchased->setPositionIdentifier($product->getPositionIdentifier());
                            $purchased->setBlockId($block_id);
                            $purchased->setBlockImage($blockData->getImage());
                            $purchased->setBlockTitle($blockData->getTitle());
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
                    //echo $sql_sales_order;die;
                    $connection->query($sql_sales_order);
                    $sql_sales_grid = "Update " . $sales_grid_table . " Set `is_plan` = '1' where `increment_id` = '".$order_id."'";
                    $connection->query($sql_sales_grid);
                  //  echo $sql_sales_order;die;
                }
            }            
        }catch(\Exception $e) {
            echo $e->getMessage();die;
        }        
    }

}