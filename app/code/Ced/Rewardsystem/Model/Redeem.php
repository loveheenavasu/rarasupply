<?php
namespace Ced\Rewardsystem\Model;
/**
* Class Custom
* @package Magestore\Webpos\Model\Total\Quote
*/
class Redeem extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
   protected $quoteValidator = null; 

    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Checkout\Model\Session $checkoutSession,
         \Magento\Framework\Registry $registry
        )
    {
      
        $this->_coreRegistry = $registry;
        $this->request = $request;
        $this->_checkoutSession = $checkoutSession;
        $this->quoteValidator = $quoteValidator;
    }
   

   public function collect(
       \Magento\Quote\Model\Quote $quote,
       \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
       \Magento\Quote\Model\Quote\Address\Total $total
   )
   {
      parent::collect($quote, $shippingAssignment, $total);
      $address             = $shippingAssignment->getShipping()->getAddress();
      $label               = 'Reward  Discount';
      
      $appliedCartDiscount = 0;
      if($this->_checkoutSession->getDiscountAmount() || $this->_checkoutSession->getDiscountAmount() == 0){
        $discountAmount = - $this->_checkoutSession->getDiscountAmount();

      $this->_checkoutSession->setDiscountAmountUsed($this->_checkoutSession->getDiscountAmount());
      if ($address->getAddressType() == 'shipping') 
      { 
          if($total->getDiscountDescription()) 
          {
            // If a discount exists in cart and another discount is applied, the add both discounts.
            $appliedCartDiscount = $total->getDiscountAmount();
            $discountAmount      = $total->getDiscountAmount()+$discountAmount;
            $label               = $total->getDiscountDescription().', '.$label;
          }    

          $total->setDiscountDescription($label);
          //$total->addTotalAmount('fee', -$discountAmount);
          $total->setDiscountAmount($discountAmount);
          $total->setBaseDiscountAmount($discountAmount);
          $total->setSubtotalWithDiscount($total->getSubtotal() + $discountAmount);
          $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $discountAmount);

          if(isset($appliedCartDiscount)) 
          {
            $total->addTotalAmount('fee', $discountAmount - $appliedCartDiscount);
            $total->addBaseTotalAmount('fee', $discountAmount - $appliedCartDiscount);
          } 
          else 
          {
            $total->addTotalAmount('fee', $discountAmount);
            $total->addBaseTotalAmount('fee', $discountAmount);
          }
      }

      if ($this->_checkoutSession->getDiscountAmount()){
          $quote->setRewardsystemBaseAmount($this->_checkoutSession->getDiscountAmount());
          $quote->setRewardsystemDiscount($this->_checkoutSession->getDiscountAmount());
      }
    } 
    
    return $this;
   }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
       if($this->_checkoutSession->getDiscountAmount() || $this->_checkoutSession->getDiscountAmount() == 0){
        return [
            'code' => 'fee',
            'title' => $this->getLabel(),
            'value' => $this->_checkoutSession->getDiscountAmount()
        ];
      }
    }
 
    /**
     * get label
     * @return string
     */
    public function getLabel()
    {
        return __('Reward  Discount');
    }
}