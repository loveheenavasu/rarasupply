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
 * Class Deleteproduct
 * @package Ced\CsDeal\Observer
 */
Class Deleteproduct implements ObserverInterface
{
    /**
     * @var \Ced\CsDeal\Model\ResourceModel\Deal\CollectionFactory
     */
    protected $dealCollectionFactory;

    /**
     * Deleteproduct constructor.
     * @param \Ced\CsDeal\Model\ResourceModel\Deal\CollectionFactory $dealCollectionFactory
     */
    public function __construct(
        \Ced\CsDeal\Model\ResourceModel\Deal\CollectionFactory $dealCollectionFactory
    )
    {
        $this->dealCollectionFactory = $dealCollectionFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $deal = $this->dealCollectionFactory->create()->addFieldToFilter('product_id', $product->getId())->getFirstItem();
        try {

            $deal->delete();
            $deal->save();
        } catch (Exception $e) {
            $this->_eventManager->addError(__('%s', $e->getMessage()));
        }
    }

}