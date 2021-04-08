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
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Rewardsystem\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class ChangeOrderStatus
 * @package Ced\Rewardsystem\Observer
 */
class ChangeOrderStatus implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Ced\Rewardsystem\Model\ResourceModel\Regisuserpoint\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * ChangeOrderStatus constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\Rewardsystem\Model\ResourceModel\Regisuserpoint\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\Rewardsystem\Model\ResourceModel\Regisuserpoint\CollectionFactory $collectionFactory
    )
    {
        $this->date = $date;
        $this->scopeConfig = $scopeConfig;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * custom event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        try {

            $date = $this->date->gmtDate();
            $expdate = "";
            $add_days = $this->scopeConfig->getValue('reward/setting/point_expiration', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($add_days) {
                $expdate = date('Y-m-d', strtotime($date . ' +' . $add_days . ' days'));
            }


            $id = $order->getId();
            $status = $order->getStatus();
            $model = $this->collectionFactory->create()->addFieldToFilter('order_id', $id)->getFirstItem();
            if ($model->getId()) {
                if ($status == 'pending') {
                    $model->setStatus('processing');
                    $model->save();
                    return;

                } else {
                    if (!$order->canInvoice()) {
                        $model->setStatus('complete');
                        if ($expdate) {
                            $model->setExpirationDate($expdate);
                        }
                        $model->save();
                        return;
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException (__($e->getMessage()));

        }
    }
}
