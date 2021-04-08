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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Block\Edit\Tab\Renderer;

/**
 * Class AdminStatus
 * @package Ced\CsDeal\Block\Edit\Tab\Renderer
 */
class AdminStatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Ced\CsDeal\Model\DealFactory
     */
    protected $dealFactory;

    /**
     * AdminStatus constructor.
     * @param \Ced\CsDeal\Model\DealFactory $dealFactory
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Ced\CsDeal\Model\DealFactory $dealFactory,
        \Magento\Backend\Block\Context $context,
        array $data = []
    )
    {
        $this->dealFactory = $dealFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $vOptionArray = $this->dealFactory->create()->getMassActionArray();
        switch ($row->getAdminStatus()) {
            case \Ced\CsDeal\Model\Deal::STATUS_APPROVED;
                return $vOptionArray[\Ced\CsDeal\Model\Deal::STATUS_APPROVED];
                break;
            case \Ced\CsDeal\Model\Deal::STATUS_NOT_APPROVED;
                return $vOptionArray[\Ced\CsDeal\Model\Deal::STATUS_NOT_APPROVED];
                break;

            default:
                return $vOptionArray[\Ced\CsDeal\Model\Deal::STATUS_PENDING];
                break;
        }
    }
}