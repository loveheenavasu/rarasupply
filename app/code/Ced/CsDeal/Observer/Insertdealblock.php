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

namespace Ced\CsDeal\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class Insertdealblock
 * @package Ced\CsDeal\Observer
 */
Class Insertdealblock implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Registry|null
     */
    public $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * Insertdealblock constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Request\Http $httpRequest
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $httpRequest
    )
    {
        $this->_coreRegistry = $registry;
        $this->httpRequest = $httpRequest;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_block = $observer->getBlock();
        $_type = $_block->getType();
        if ($_type == 'Magento\Catalog\Pricing\Render') {
            if (!$this->_coreRegistry->registry('view_id_deal_in')) {
                $execute = true;
            }

            if ($this->httpRequest->getFullActionName() == 'catalog_product_view' && $execute) {
                $this->_coreRegistry->registry('view_id_deal_in', '7');
                $_child = clone $_block;
                $_child->setType('core/template');
                $_block->setChild('child' . $_block->getProduct()->getId(), $_child);
                $_block->setTemplate('Ced_CsDeal::csdeal/show/deal.phtml');
            } else if ($this->httpRequest->getFullActionName() == 'csmarketplace_vshops_view' || $this->httpRequest->getFullActionName() == 'catalog_category_view') {
                $_child = clone $_block;
                $_child->setType('view/template');
                $_block->setChild('child' . $_block->getProduct()->getId(), $_child);
                $_block->setTemplate('Ced_CsDeal::csdeal/show/deal.phtml');
            }
        }
    }

}