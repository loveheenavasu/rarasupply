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
 * Class ChangeStatus
 * @package Ced\Rewardsystem\Observer
 */
class ChangeStatus implements ObserverInterface
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
    protected $regisuserpointCollectionFactory;

    /**
     * ChangeStatus constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\Rewardsystem\Model\ResourceModel\Regisuserpoint\CollectionFactory $regisuserpointCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\Rewardsystem\Model\ResourceModel\Regisuserpoint\CollectionFactory $regisuserpointCollectionFactory
    )
    {
        $this->date = $date;
        $this->scopeConfig = $scopeConfig;
        $this->regisuserpointCollectionFactory = $regisuserpointCollectionFactory;
    }

    /**
     * custom event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        try {
            if (!$order->canInvoice()) {
                $date = $this->date->gmtDate();
                $add_days = $this->scopeConfig->getValue('reward/setting/point_expiration', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $expdate = "";
                if ($add_days) {
                    $expdate = date('Y-m-d', strtotime($date . ' +' . $add_days . ' days'));
                }
                $id = $order->getId();

                $status = $order->getStatus();

                $model = $this->regisuserpointCollectionFactory->create()->addFieldToFilter('order_id', $id)->getFirstItem();

                if ($model->getId()) {
                    if ($status == 'pending') {
                        $model->setStatus('processing');
                        $model->save();
                        return;

                    } else {
                        if (!$order->canShip()) {
                            $model->setStatus('complete');
                            if ($expdate) {
                                $model->setExpirationDate($expdate);
                            }

                        } else {
                            $model->setStatus('processing');
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
