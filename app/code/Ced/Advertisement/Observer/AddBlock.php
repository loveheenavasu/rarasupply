<?php

namespace Ced\Advertisement\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class AddBlock implements ObserverInterface
{   
    protected $_request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Quote\Model\QuoteFactory $quote
    ) { 
        $this->quote = $quote; 
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_checkoutSession = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $plan_data = [];

        $data = $this->_request->getPost();
        $item = $observer->getEvent()->getData('quote_item'); 
      //  print_r(json_encode($item->getData()));die;
        //$quote_item = ( $item->getParentItem() ? $item->getParentItem() : $item );  
        if(isset($data['block_id']) && $data['block_id']){
            $item->setData('block_id', $data['block_id']);
            //$item->save();
        }
    }

}