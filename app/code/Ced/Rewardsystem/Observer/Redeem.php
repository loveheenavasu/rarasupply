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
* @package     Ced_Rewardsystem
* @author   	 CedCommerce Core Team <connect@cedcommerce.com >
* @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
* @license      http://cedcommerce.com/license-agreement.txt
*/  
namespace Ced\Rewardsystem\Observer;
 
use Magento\Framework\Event\ObserverInterface;
 
class Redeem implements ObserverInterface
{
    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry
        )
    {
        $this->_session = $session;
        $this->_coreRegistry = $registry;
        $this->_checkoutSession = $checkoutSession;
    }
    /**
     * custom event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	
     if($this->_checkoutSession->getDiscountAmount()){
       $quote         =  $observer->getEvent()->getQuote();
       $quoteid       =  $quote->getId();
       $discountAmount=  $this->_checkoutSession->getDiscountAmount();

       if($quoteid) {

         if($discountAmount>0) {

           $total=$quote->getBaseSubtotal();
           $quote->setSubtotal(0);
           $quote->setBaseSubtotal(0);
           $quote->setSubtotalWithDiscount(0);
           $quote->setBaseSubtotalWithDiscount(0);
           $quote->setGrandTotal(0);
           $quote->setBaseGrandTotal(0);
           $itemAllowed = $quote->isVirtual()? ('billing') : ('shipping'); 

           foreach ($quote->getAllAddresses() as $address) {

             $quote->setSubtotal((float) $quote->getSubtotal() + $address->getSubtotal());
             $quote->setBaseSubtotal((float) $quote->getBaseSubtotal() + $address->getBaseSubtotal());
             $quote->setSubtotalWithDiscount((float) $quote->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount());
             $quote->setBaseSubtotalWithDiscount((float) $quote->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount());
             $quote->setGrandTotal((float) $quote->getGrandTotal() + $address->getGrandTotal());
             $quote->setBaseGrandTotal((float) $quote->getBaseGrandTotal() + $address->getBaseGrandTotal());
             $quote ->save();
             $quote->setGrandTotal($quote->getBaseSubtotal()-$discountAmount)
                   ->setBaseGrandTotal($quote->getBaseSubtotal()-$discountAmount)
                   ->setSubtotalWithDiscount($quote->getBaseSubtotal()-$discountAmount)
                   ->setBaseSubtotalWithDiscount($quote->getBaseSubtotal()-$discountAmount)
                   ->save(); 


                  if($address->getAddressType()==$itemAllowed) {
                     $address->setSubtotalWithDiscount((float)$address->getSubtotalWithDiscount()-$discountAmount);
                     $address->setGrandTotal((float) $address->getGrandTotal()-$discountAmount);
                     $address->setBaseSubtotalWithDiscount((float)$address->getBaseSubtotalWithDiscount()-$discountAmount);
                     $address->setBaseGrandTotal((float)$address->getBaseGrandTotal()-$discountAmount);
                  if($address->getDiscountDescription()){
                  $address->setDiscountAmount(-($address->getDiscountAmount()-$discountAmount));
                       $address->setDiscountDescription($address->getDiscountDescription().', Custom Discount');
                  $address->setBaseDiscountAmount(-($address->getBaseDiscountAmount()-$discountAmount));
                  }else {
                      $address->setDiscountAmount(-($discountAmount));
                      $address->setDiscountDescription('Custom Discount');
                      $address->setBaseDiscountAmount(-($discountAmount));
                  }
                  $address->save();
             }//end: if

          } //end: foreach

          foreach($quote->getAllItems() as $item){
               $rat = $item->getPriceInclTax()/$total;
               $ratdisc = $discountAmount*$rat;
               $item->setDiscountAmount(($item->getDiscountAmount()+$ratdisc) * $item->getQty());
               $item->setBaseDiscountAmount(($item->getBaseDiscountAmount()+$ratdisc) * $item->getQty())->save();
          }
          
          }
        }
        $this->_checkoutSession->unsDiscountAmount();
    }
  }
}