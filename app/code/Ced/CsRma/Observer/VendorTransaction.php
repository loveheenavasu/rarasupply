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
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRma\Observer;

use Magento\Framework\Event\ObserverInterface;

class VendorTransaction implements ObserverInterface
{
    /**
     * @var \Ced\CsRma\Model\ResourceModel\Request\CollectionFactory
     */
    protected $rmaRequestFactory;

    /**
     * VendorTransaction constructor.
     * @param \Ced\CsRma\Model\ResourceModel\Request\CollectionFactory $rmaRequestFactory
     */
    public function __construct(
        \Ced\CsRma\Model\ResourceModel\Request\CollectionFactory $rmaRequestFactory
    )
    {
        $this->rmaRequestFactory = $rmaRequestFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $obj = $observer->getEvent()->getVendorTrans();
        $package = $obj->getOrderArray();
        $package['headers']['admin_fee'] = 'Admin RMA Fee';
        $package['pricing_columns'][] = 'admin_fee';
        foreach ($package['values'] as $key => $values) {
            $rma_vorder = $this->rmaRequestFactory->create()
                ->addFieldToFilter('order_id', $key);
            $vadjustment = 0;
            foreach ($rma_vorder->getData() as $val) {
                if ($val['status'] == 'Approved' && $val['resolution_requested'] == 'Refund') {

                    $vadjustment += floatval($val['additional_refund']);
                    $vadjustment += floatval($val['refund_amount']);
                    $vadjustment -= floatval($val['vendor_adjustment_amount']);
                }
            }
            $fee = -1 * $vadjustment;
            $package['values'][$key]['admin_fee'] = $fee;
        }
        $obj->setOrderArray($package);
        return $obj;
    }
}
