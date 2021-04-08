<?php

namespace Ced\Advertisement\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class QuoteItemRemove implements ObserverInterface
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
            $quoteItem = $observer->getQuoteItem();
            $product_id = $quoteItem->getProductId();
            if($product_id && isset($plan_data)){
                unset($plan_data[$product_id]);
            }
            
        }catch(\Exception $e) {
            echo $e->getMessage();die;
        }        
    }

}