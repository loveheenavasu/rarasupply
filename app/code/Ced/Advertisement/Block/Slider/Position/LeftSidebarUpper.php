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
class LeftSidebarUpper extends \Magento\Framework\View\Element\Template
{
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
                    ->create('Ced\Advertisement\Model\ResourceModel\Purchased\Collection');

        $purchased->getSelect()->join(array('ced_advertisement_block'),
                                        'main_table.block_id = ced_advertisement_block.id AND ced_advertisement_block.status = 1'); 
        $purchased->addFieldToFilter('position_identifier','show_ad_in_upper_left_sidebar')
                    ->addFieldToFilter('main_table.status', 1);

        return $purchased;
    }
}