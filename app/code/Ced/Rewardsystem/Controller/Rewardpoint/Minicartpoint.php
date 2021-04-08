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
 * @package     Ced_Rewardsystem
 * @author     CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Rewardsystem\Controller\Rewardpoint;

/**
 * Class Minicartpoint
 * @package Ced\Rewardsystem\Controller\Rewardpoint
 */
class Minicartpoint extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $cartFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Minicartpoint constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;
        $this->cartFactory = $cartFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $this->_checkoutSession->unsProductpoint();
        $resultJson = $this->resultJsonFactory->create();

        $productshow = $this->scopeConfig;
        $sameProductPoint = $productshow->getValue('reward/setting/product_point', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $maxOrderPoint = $productshow->getValue('reward/setting/max_point', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $maxOrderPointEnable = $productshow->getValue('reward/setting/max_point_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $items = $this->cartFactory->create()->getItems();
        $sku = [];
        if (!empty($items)) {
            foreach ($items as $key => $item) {
                if (in_array($item->getSku(), $sku)) {
                    continue;
                }
                $sku[] = $item->getSku();
                $updatepoint = 0;
                $productCollection = $this->productFactory->create();
                $productCollection->load($item->getProduct()->getId());
                $point = $productCollection->getCedRpoint();

                $updatepoint = $this->_checkoutSession->getProductpoint();
                if ($point !== '0') {
                    if ($point) {
                        $minicartpoint = $updatepoint + ($point * $item->getQty());
                        $this->_checkoutSession->setProductpoint($minicartpoint);

                    } else {
                        if ($sameProductPoint) {
                            $minicartpoint = $updatepoint + ($sameProductPoint * $item->getQty());
                            $this->_checkoutSession->setProductpoint($minicartpoint);
                        } else {
                            $this->_checkoutSession->getProductpoint();
                        }
                    }

                } else {
                    $this->_checkoutSession->setProductpoint(0);
                }

            }
            if (($maxOrderPoint < $this->_checkoutSession->getProductpoint()) && ($maxOrderPointEnable == 1)) {
                $this->_checkoutSession->setTotalProductpoint($maxOrderPoint);
                $resultJson->setData($maxOrderPoint);
                return $resultJson;

            } else {
                $this->_checkoutSession->setTotalProductpoint($this->_checkoutSession->getProductpoint());
                $resultJson->setData($this->_checkoutSession->getProductpoint());
                return $resultJson;
            }
        }
    }
}
