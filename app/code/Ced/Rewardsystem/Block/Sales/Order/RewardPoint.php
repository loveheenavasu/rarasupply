<?php

namespace Ced\Rewardsystem\Block\Sales\Order;
use Magento\Framework\View\Element\Template\Context;

class RewardPoint extends \Magento\Framework\View\Element\Template
{

    /**
     * @param Context $context
     * @param Data $paymentFeeHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get totals source object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Create the Checkout Fee totals summary
     *
     * @return $this
     */
    public function initTotals()
    {
        if ($this->getSource() instanceof \Magento\Sales\Model\Order) {
            
            $order = $this->getSource();
        } else {
          
            $order = $this->getSource()->getOrder();
        }


        $rewardDiscount = $order->getRewardsystemDiscount();
        $baseRewardDiscount = $order->getRewardsystemBaseDiscount();

        if ($rewardDiscount) {
            // Add our total information to the set of other totals
            $total = new \Magento\Framework\DataObject(
                [
                    'code' => $this->getNameInLayout(),
                    'label' => __('Reward Discount'),
                    'value' => $rewardDiscount,
                    'base_value' => $baseRewardDiscount
                ]
            );
            if ($this->getBeforeCondition()) {
                $this->getParentBlock()->addTotalBefore($total, $this->getBeforeCondition());
            } else {
                $this->getParentBlock()->addTotal($total, $this->getAfterCondition());
            }
        }
        return $this;
    }
}
