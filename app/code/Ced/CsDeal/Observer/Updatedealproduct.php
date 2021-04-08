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
 * Class Updatedealproduct
 * @package Ced\CsDeal\Observer
 */
Class Updatedealproduct implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Updatedealproduct constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->productFactory = $productFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $deal = $observer->getDeal();
        $product = $this->productFactory->create()->load($deal->getProductId());
        try {
            if ($deal->getStatus() == 'running' || $deal->getStatus() == 'enabled') {
                $product->setSpecialPrice($deal->getDealPrice());
                $product->setSpecialFromDate($deal->getStartDate());
                $product->setSpecialToDate($deal->getEndDate());
            } else {
                $product->setSpecialPrice('');
                $product->getResource()->saveAttribute($product, 'special_price');
                $product->save();
            }
            $product->save();
        } catch (Exception $e) {
            $this->_eventManager->addError(__('%s', $e->getMessage()));
        }
    }

}
