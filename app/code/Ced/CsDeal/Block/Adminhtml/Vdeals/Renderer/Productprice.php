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
 * Class Productprice
 * @package Ced\CsDeal\Block\Adminhtml\Vdeals\Renderer
 */
class Productprice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Productprice constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    )
    {
        $this->pricingHelper = $pricingHelper;
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * Render approval link in each vendor row
     * @param Varien_Object $row
     * @return String
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $product = $this->productFactory->create()->load($row->getData($this->getColumn()->getIndex()));
        return $this->pricingHelper->currency($product->getPrice(), true, false);
    }
}