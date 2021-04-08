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

namespace Ced\Rewardsystem\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class Assignloginuserpoint
 * @package Ced\Rewardsystem\Observer
 */
class Assignloginuserpoint implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ced\Rewardsystem\Model\RegisuserpointFactory
     */
    protected $regisuserpointFactory;

    /**
     * Assignloginuserpoint constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory
    )
    {
        $this->_coreRegistry = $registry;
        $this->regisuserpointFactory = $regisuserpointFactory;
    }

    /**
     * custom event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_coreRegistry->registry('customerid')) {
            return $this;
        } else {
            $event = $observer->getEvent();
            $customer = $event->getCustomer();
            $model = $this->regisuserpointFactory->create();
            $customerid = $customer->getId();
            $model->setCustomerId($customerid);
            $model->setPoint('10');
            $this->_coreRegistry->register('customerid', $customerid);
            $model->save();
        }

        return $this;
    }
}