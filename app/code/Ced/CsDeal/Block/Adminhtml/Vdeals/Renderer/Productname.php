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
 * Class Productname
 * @package Ced\CsDeal\Block\Adminhtml\Vdeals\Renderer
 */
class Productname extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Productname constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Context $context,
        array $data = []
    )
    {
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
        return $this->productFactory->create()->load($row->getData($this->getColumn()->getIndex()))->getName();
    }
}