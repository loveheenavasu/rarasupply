<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsDeal
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Block\Adminhtml\Vdeals\Renderer;

/**
 * Class Action
 * @package Ced\CsDeal\Block\Adminhtml\Vdeals\Renderer
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Render approval link in each vendor row
     * @param Varien_Object $row
     * @return String
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $sure = "you Sure?";
        if ($row->getAdminStatus() == \Ced\CsDeal\Model\Deal::STATUS_APPROVED)
            $html = '<a href="' . $this->getUrl('csdeal/vdeals/changeStatus/status/0/deal_id/' . $row->getId()) . '" title="' . __("Click to Disapprove") . '" onclick="return confirm(\'Are you sure, You want to disapprove?\')">' . __("Disapprove") . '</a>';
        if ($row->getAdminStatus() == \Ced\CsDeal\Model\Deal::STATUS_PENDING)
            $html = '<a href="' . $this->getUrl('csdeal/vdeals/changeStatus/status/1/deal_id/' . $row->getId()) . '"  title="' . __("Click to Approve") . '" onclick="return confirm(\'Are you sure, You want to approve?\')">' . __("Approve") . '</a>
        		 | <a href="' . $this->getUrl('csdeal/vdeals/changeStatus/status/0/deal_id/' . $row->getId()) . '" title="' . __("Click to Disapprove") . '" onclick="return confirm(\'Are you sure, You want to disapprove?\')">' . __("Disapprove") . '</a>';
        if ($row->getAdminStatus() == \Ced\CsDeal\Model\Deal::STATUS_NOT_APPROVED)
            $html = '<a href="' . $this->getUrl('csdeal/vdeals/changeStatus/status/1/deal_id/' . $row->getId()) . '"  title="' . __("Click to Approve") . '" onclick="return confirm(\'Are you sure, You want to approve?\')">' . __("Approve") . '</a>';
        return $html;
    }
}