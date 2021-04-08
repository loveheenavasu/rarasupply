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
 * Class Vendorname
 * @package Ced\CsDeal\Block\Adminhtml\Vdeals\Renderer
 */
class Vendorname extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * Vendorname constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Backend\Block\Context $context,
        array $data = []
    )
    {
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context, $data);
    }

    /**
     * Render approval link in each vendor row
     * @param Varien_Object $row
     * @return String
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return $this->vendorFactory->create()->load($row->getData($this->getColumn()->getIndex()))->getName() . ' (' . $this->vendorFactory->create()->load($row->getData($this->getColumn()->getIndex()))->getEmail() . ')';
    }
}