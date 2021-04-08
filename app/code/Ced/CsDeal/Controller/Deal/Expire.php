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

namespace Ced\CsDeal\Controller\Deal;

use Magento\Framework\App\Action\Context;

/**
 * Class Expire
 * @package Ced\CsDeal\Controller\Deal
 */
class Expire extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Ced\CsDeal\Model\DealFactory
     */
    protected $dealFactory;

    /**
     * Expire constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\CsDeal\Model\DealFactory $dealFactory
     * @param Context $context
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsDeal\Model\DealFactory $dealFactory,
        Context $context
    )
    {
        $this->productFactory = $productFactory;
        $this->dealFactory = $dealFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $post_data = $this->getRequest()->getPost('product_id');
        $product = $this->productFactory->create()->load($post_data);
        try {
            $product->setSpecialPrice(null);
            $product->getResource()->saveAttribute($product, 'special_price');
            $product->save();
            $deal = $this->dealFactory->create()->load($post_data, 'product_id');
            $deal->delete();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
    }
}