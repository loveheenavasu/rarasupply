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

namespace Ced\Rewardsystem\Block;

use \Ced\Rewardsystem\Helper\Data;

/**
 * Class Grid
 * @package Ced\Rewardsystem\Block
 */
class Grid extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var Data
     */
    protected $rewardsystem_helper;

    /**
     * @var \Ced\Rewardsystem\Model\RegisuserpointFactory
     */
    protected $regisuserpointFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Grid constructor.
     * @param \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param Data $rewardsystem_helper
     * @param array $data
     */
    public function __construct(
        \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        Data $rewardsystem_helper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->currentCustomer = $currentCustomer;
        $this->rewardsystem_helper = $rewardsystem_helper;
        $this->regisuserpointFactory = $regisuserpointFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->_scopeConfig = $context->getScopeConfig();
        $this->priceCurrency = $priceCurrency;
    }

    protected function _construct()
    {
        parent::_construct();

        $this->pageConfig->getTitle()->set(__('My Reward Points'));
        $customerId = $this->currentCustomer->getCustomerId();
        $point = $this->regisuserpointFactory->create()->getCollection()->addFieldToFilter('customer_id', $customerId);
        $this->setPoint($point);
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager',
            'rewardsystem.point.list.pager'
        )->setAvailableLimit(array(5 => 5))
            ->setCollection($this->getPoint());
        $this->setChild('rewardsystem.point.list.pager', $pager);
        $this->getPoint()->load();

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('rewardsystem.point.list.pager');
    }

    /**
     * @return int
     */
    public function subtotal()
    {
        $customerId = $this->currentCustomer->getCustomerId();
        $subtotal = $this->rewardsystem_helper->getCustomerWisePointSheet($customerId);
        return !empty($subtotal[$customerId]['points']) ? $subtotal[$customerId]['points'] : 0;
    }

    /**
     * @return string
     */
    public function getReferUrl()
    {
        $customerId = $this->currentCustomer->getCustomerId();
        $RewardModel = $this->regisuserpointFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('is_register', 1)->getfirstItem();
        $referCode = $RewardModel->getReferCode();
        $url = $this->getUrl('customer/account/create/', ['refer_code' => $referCode]);
        return $url;
    }

    /**
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPointsValue()
    {
        $currencyCode = $this->_storeManager->getStore(null)->getBaseCurrencyCode();

        $points = $this->_scopeConfig->getValue('reward/setting/point');
        $pointsPrice = $this->_scopeConfig->getValue('reward/setting/point_value');

        $points = !empty((int)$points) ? (int)$points : 1;
        $PointinPrice = $this->priceCurrency->format($pointsPrice / $points, false, 2, null, $currencyCode);

        return $PointinPrice;
    }

}


