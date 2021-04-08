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
 * Class Productsaveafter
 * @package Ced\CsDeal\Observer
 */
Class Productsaveafter implements ObserverInterface
{
    /**
     * @var \Ced\CsDeal\Model\ResourceModel\Deal\CollectionFactory
     */
    protected $dealCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Productsaveafter constructor.
     * @param \Ced\CsDeal\Model\ResourceModel\Deal\CollectionFactory $dealCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Ced\CsDeal\Model\ResourceModel\Deal\CollectionFactory $dealCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->dealCollectionFactory = $dealCollectionFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $product = $observer->getProduct();
        if ($product->getId()) {
            $deal = $this->dealCollectionFactory->create()
                ->addFieldToFilter('product_id', $product->getId())->getFirstItem();

            $dealPro = $this->productFactory->create()->load($product->getId());

            if ($deal->getDealId() && $dealPro->getId()) {
                try {
                    $price = $dealPro->getSpecialPrice();
                    $fromDate = $dealPro->getSpecialFromDate();
                    $toDate = $dealPro->getSpecialToDate();
                    if ($price) {
                        $deal->setEndDate($toDate);
                        $deal->setStartDate($fromDate);

                        $deal->save();
                    }
                } catch (\Exception $e) {

                    $this->_eventManager->addError(__('%s', $e->getMessage()));
                }
            }
        }
    }

}
