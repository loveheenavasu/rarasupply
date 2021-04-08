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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Rewardsystem\Block;

/**
 * Class Points
 * @package Ced\Rewardsystem\Block
 */
class Points extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * @var string
     */
    protected $_template = 'Ced_Rewardsystem::reward/transactions.phtml';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

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
     * Points constructor.
     * @param \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->date = $date;
        $this->currentCustomer = $currentCustomer;
        $this->regisuserpointFactory = $regisuserpointFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->_scopeConfig = $context->getScopeConfig();
        $this->priceCurrency = $priceCurrency;
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_controller = 'rewardpoint_points';
        $this->_blockGroup = 'Ced_Rewardsystem';
        $this->_headerText = __('Reward');
        parent::_construct();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Container
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Ced\Rewardsystem\Block\Points\Grid', 'ced_reward_transaction_grid')
        );
        return parent::_prepareLayout();
    }


    /**
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * @return int
     */
    public function subtotal()
    {
        $date = $this->date->gmtDate();
        $curdate = strtotime($date);
        $customerId = $this->currentCustomer->getCustomerId();
        $point = $this->regisuserpointFactory->create();
        $collection = $point->getCollection()->addFieldToFilter('customer_id', $customerId)->addFieldToFilter('status', 'complete');
        $totalpoint = $collection->getData();
        $subtotal = 0;

        foreach ($totalpoint as $key => $value) {

            $mydate = strtotime($value['expiration_date']);
            if ($value['is_register']) {
                $subtotal = $subtotal + $value['point'];
            } elseif ($curdate <= $mydate || !isset($value['expiration_date'])) {
                $subtotal = $subtotal + $value['point'];
            }
        }

        $usedPointCollection = $point->getCollection()->addFieldToFilter('customer_id', $customerId);
        $totalremainingpoint = $usedPointCollection->getData();
        $usedpoint = 0;
        foreach ($totalremainingpoint as $key => $value) {
            $usedpoint = $usedpoint + $value['point_used'];
        }

        return $subtotal - $usedpoint;
    }

    /**
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPointsValue()
    {
        $currencyCode = $this->_storeManager->getStore(null)->getBaseCurrencyCode();

        $store = $this->_scopeConfig;
        $points = $store->getValue('reward/setting/point');
        $pointsPrice = $store->getValue('reward/setting/point_value');

        $PointinPrice = $this->priceCurrency->format($pointsPrice / $points, false, 2, null, $currencyCode);

        return $PointinPrice;
    }


}