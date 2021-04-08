<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 20/11/18
 * Time: 6:07 PM
 */

namespace Ced\Rewardsystem\Block\Points\Grid;


use Ced\Rewardsystem\Helper\Data;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class RedeemPoints extends AbstractRenderer
{
    protected $rewardsystem_helper;
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry,
        Data $rewardsystem_helper,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->rewardsystem_helper = $rewardsystem_helper;
        $this->registry = $registry;
    }

    public function render(\Magento\Framework\DataObject $row){
        $recordId = $row->getData($this->getColumn()->getIndex());
        $point_data = $this->registry->registry('points_data');
        $current_row_data = array_column($point_data, 'redeem_points', 'id');

        return ($recordId && !empty($current_row_data[$recordId]) ) ? $current_row_data[$recordId] : '-';
    }
}