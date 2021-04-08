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

namespace Ced\Rewardsystem\Block\Checkout\Payment;

use \Ced\Rewardsystem\Helper\Data;

/**
 * Class Redeem
 * @package Ced\Rewardsystem\Block\Checkout\Payment
 */
class Redeem extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Data
     */
    protected $rewardsystem_helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Ced\Rewardsystem\Model\RegisuserpointFactory
     */
    protected $regisuserpointFactory;

    /**
     * Redeem constructor.
     * @param \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Data $rewardsystem_helper
     * @param array $data
     */
    public function __construct(
        \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        Data $rewardsystem_helper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->date = $date;
        $this->currentCustomer = $currentCustomer;
        $this->rewardsystem_helper = $rewardsystem_helper;
        $this->regisuserpointFactory = $regisuserpointFactory;
    }

    /**
     * @return mixed
     */
    public function TotalPointss()
    {
        $customerId = $this->currentCustomer->getCustomerId();
        $subtotal = $this->rewardsystem_helper->getCustomerWisePointSheet($customerId);
        $point = !empty($subtotal[$customerId]['points']) ? $subtotal[$customerId]['points'] : 0;

        $currentlyusedpoint = ($this->_checkoutSession->getUsedRPoints()) ? $this->_checkoutSession->getUsedRPoints() : 0;

        $results['currusedPoints'] = $currentlyusedpoint;
        $results['totalPoint'] = $point - $currentlyusedpoint;
        $results['actualtotalPoint'] = $point;
        $results['customerLogin'] = $this->_customerSession->isLoggedIn();

        return $results;
    }

    /**
     * @return mixed
     */
    public function TotalPoint()
    {
        $date = $this->date->gmtDate();
        $date = strtotime($date);
        $customerId = $this->currentCustomer->getCustomerId();
        $point = $this->regisuserpointFactory->create();
        $collection = $point->getCollection()->addFieldToFilter('customer_id', $customerId)->addFieldToFilter('status', 'complete');
        $totalpoint = $collection->getData();

        $subtotal = 0;
        foreach ($totalpoint as $key => $value) {
            $mydate = strtotime($value['expiration_date']);
            if ($value['expiration_date'] == null) {
                $subtotal = $subtotal + $value['point'];
            } elseif ($date <= $mydate) {
                $subtotal = $subtotal + $value['point'];
            }
        }

        $usedPointCollection = $point->getCollection()->addFieldToFilter('customer_id', $customerId);
        $totalremainingpoint = $usedPointCollection->getData();
        $usedpoint = 0;
        $currentlyusedpoint = 0;
        foreach ($totalremainingpoint as $key => $value) {
            $usedpoint = $usedpoint + $value['point_used'];
        }

        if ($this->_checkoutSession->getUsedRPoints()) {
            $currentlyusedpoint = $this->_checkoutSession->getUsedRPoints();
        }
        $results['currusedPoints'] = $currentlyusedpoint;
        $results['totalPoint'] = $subtotal - $usedpoint - $currentlyusedpoint;
        $results['customerLogin'] = $this->_customerSession->isLoggedIn();
        $results['actualtotalPoint'] = $subtotal - $usedpoint;

        return $results;
    }

}
