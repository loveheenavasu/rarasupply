<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Advertisement\Block\Slider\Position;

/**
 * Customer dashboard block
 *
 * @api
 * @since 100.0.2
 */
class Checkout extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'slider/position/checkout.phtml';
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectInterface;
        parent::__construct($context, $data);
    }

    public function sliderData(){
        $current_date = date("Y-m-d");
        $adsAllowedPerPosition = $this->scopeConfig->getValue('advertisement/ads_settings/ad_qty', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $purchased = $this->_objectManager
                    ->create('Ced\Advertisement\Model\ResourceModel\Purchased\Collection')
                    ->addFieldToFilter('position_identifier','show_ad_in_checkout')
                    ->addFieldToFilter('status', 1);
        $purchased->getSelect()->orderRand()->limit($adsAllowedPerPosition);
        return $purchased;
    }
}