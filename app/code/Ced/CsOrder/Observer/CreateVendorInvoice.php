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
 * @category  Ced
 * @package   Ced_CsOrder
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsOrder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;

/**
 * Class CreateVendorInvoice
 * @package Ced\CsOrder\Observer
 */
Class CreateVendorInvoice implements ObserverInterface
{
    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $marketplacehelper;

    /**
     * @var \Ced\CsOrder\Model\InvoiceFactory
     */
    protected $vinvoice;

    /**
     * @var \Ced\CsMarketplace\Model\Vorders
     */
    protected $vorders;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var Session
     */
    protected $session;

    /**
     * CreateVendorInvoice constructor.
     * @param \Ced\CsOrder\Model\InvoiceFactory $vinvoice
     * @param \Ced\CsMarketplace\Helper\Data $marketplacehelper
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Ced\CsOrder\Model\InvoiceFactory $vinvoice,
        \Ced\CsMarketplace\Helper\Data $marketplacehelper,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        Session $customerSession,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->marketplacehelper = $marketplacehelper;
        $this->vinvoice = $vinvoice;
        $this->vorders = $vorders;
        $this->request = $request;
        $this->session = $customerSession;
    }

    /**
     * Adds catalog categories to top menu
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $postItems = $this->request->getPost('invoice');
        $postItems = (isset($postItems['items'])) ? $postItems['items'] : [];

        if (empty($postItems)) {
            $params = $this->request->getParam('items');
            $allItem = json_decode($params, true);
            if (!empty($allItem)) {
                foreach ($allItem as $item) {
                    $postItems[$item['item_id']] = $item['quantity'];
                }
            }else{
                $invoiceObj = $observer->getEvent()->getInvoice();
                $orderObj = $invoiceObj->getOrder();

                $_sessionVendorId = $this->session->getVendorId();
                if($_sessionVendorId){
                    foreach($orderObj->getAllVisibleItems() as $item) {
                        $vendorId = $item->getVendorId();
                        if($_sessionVendorId == $vendorId) {
                            $postItems[$item->getId()] = $item->getQtyOrdered();
                        }
                    }
                }else{
                    foreach($orderObj->getAllVisibleItems() as $item) {
                        $vendorId = $item->getVendorId();
                        $postItems[$item->getId()] = $item->getQtyOrdered();
                    }
                }
            }
        }

        $invoice = $observer->getInvoice();
        $allItems = $invoice->getAllItems();
        $invoiceVendor = [];

        foreach ($allItems as $item) {
            if (isset($postItems[$item->getOrderItemId()]) && $postItems[$item->getOrderItemId()] > 0) {
                $vendorId = $item->getVendorId();
                $invoiceVendor[$vendorId] = $vendorId;
            }
        }
        foreach ($invoiceVendor as $vendorId) {
            $vInvoice = $this->vinvoice->create();
            try {
                $id = $invoice->getId();
                if ($vorder = $this->vorders->getVorderByInvoice($invoice)) {

                    $vInvoice->setInvoiceId($id);
                    $vInvoice->setVendorId($vendorId);
                    $vInvoice->setInvoiceOrderId($invoice->getOrderId());
                    if ($vInvoice->canInvoiceIncludeShipment($invoice)) {
                        $vInvoice->setShippingCode($vorder->getCode());
                        $vInvoice->setShippingDescription($vorder->getShippingDescription());
                        $vInvoice->setBaseShippingAmount($vorder->getBaseShippingAmount());
                        $vInvoice->setShippingAmount($vorder->getShippingAmount());
                    }
                    $vInvoice->save();
                }
            } catch (\Exception $e) {
                $this->marketplacehelper->logException($e);
            }
        }
    }
}
