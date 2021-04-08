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

namespace Ced\CsRma\Helper;

/**
 * Class OrderDetail
 * @package Ced\CsRma\Helper
 */
class OrderDetail extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var Data
     */
    protected $rmaDataHelper;

    /**
     * @var \Ced\CsRma\Model\RmaitemsFactory
     */
    protected $rmaItemFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\ItemFactory
     */
    protected $invoiceItemFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory
     */
    protected $shipmentItemFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $orderItemCollectionFactory;

    /**
     * @var \Ced\CsRma\Model\RequestFactory
     */
    protected $rmaRequestFactory;

    /**
     * @var Config
     */
    protected $rmaConfigHelper;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;

    /**
     * @var \Magento\Sales\Api\CreditmemoManagementInterface
     */
    protected $creditmemoManagement;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $salesordercollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

    /**
     * OrderDetail constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param Data $rmaDataHelper
     * @param \Ced\CsRma\Model\RmaitemsFactory $rmaItemFactory
     * @param \Magento\Sales\Model\OrderFactory $salesOrderFactory
     * @param \Magento\Sales\Model\Order\Invoice\ItemFactory $invoiceItemFactory
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory $shipmentItemFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param \Ced\CsRma\Model\RequestFactory $rmaRequestFactory
     * @param Config $rmaConfigHelper
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
     * @param \Magento\Sales\Model\Order\ItemFactory $itemFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesordercollectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Ced\CsRma\Helper\Data $rmaDataHelper,
        \Ced\CsRma\Model\RmaitemsFactory $rmaItemFactory,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Magento\Sales\Model\Order\Invoice\ItemFactory $invoiceItemFactory,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory $shipmentItemFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Ced\CsRma\Model\RequestFactory $rmaRequestFactory,
        \Ced\CsRma\Helper\Config $rmaConfigHelper,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement,
        \Magento\Sales\Model\Order\ItemFactory $itemFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesordercollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $requestInterface
    )
    {

        $this->dateTime = $dateTime;
        $this->invoiceItemFactory = $invoiceItemFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->rmaItemFactory = $rmaItemFactory;
        $this->rmaRequestFactory = $rmaRequestFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->shipmentItemFactory = $shipmentItemFactory;
        $this->rmaConfigHelper = $rmaConfigHelper;
        $this->rmaDataHelper = $rmaDataHelper;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->creditmemoLoader = $creditmemoLoader;
        $this->eventManager = $context->getEventManager();
        $this->messageManager = $messageManager;
        $this->creditmemoManagement = $creditmemoManagement;
        $this->itemFactory = $itemFactory;
        $this->salesordercollectionFactory = $salesordercollectionFactory;
        $this->logger = $logger;
        $this->requestInterface = $requestInterface;

        parent::__construct($context);
    }

    /**
     * @return array of requested order id
     */
    public function getRequestDetailsOrder($postOrder, $vendorId)
    {
        $dataList = [];
        $order = $this->salesOrderFactory->create()
            ->loadByIncrementId($postOrder['order_id']);

        if ($order->getStoreId()) {
            $dataList['store_id'] = $order->getStoreId();
            $dataList['website_id'] = $order->getStore()->getWebsiteId();
        }

        if (isset($postOrder['order_id']) && $postOrder['order_id'] != null)
            $dataList['order_id'] = $postOrder['order_id'];
        else
            return false;

        $dataList['customer_id'] = $order->getCustomerId();
        $dataList['external_link'] = $this->rmaDataHelper->getExternalLink();
        $dataList['status'] = 'Pending';
        $dataList['approval_code'] = $this->rmaDataHelper->getApprovalCode($order->getCustomerFirstname(), $postOrder['order_id']);

        if ((!isset($postOrder['rma_id']) || $postOrder['rma_id'] == null) && isset($dataList['order_id']))
            $dataList['rma_id'] = $this->rmaDataHelper->getRmaID($dataList['order_id'], $vendorId);

        if (isset($postOrder['rma_reason']) && $postOrder['rma_reason'] != null)
            $dataList['reason'] = $postOrder['rma_reason'];

        if (isset($postOrder['rma_pk_condition']) && $postOrder['rma_pk_condition'] != null)
            $dataList['package_condition'] = $postOrder['rma_pk_condition'];

        if (isset($postOrder['rma_resolution']) && $postOrder['rma_resolution'] != null)
            $dataList['resolution_requested'] = $postOrder['rma_resolution'];

        if ($order->getCustomerIsGuest() == 1) {

            $billingAddress = $order->getBillingAddress();
            if (isset($postOrder['email']) && $postOrder['email'] != null && $billingAddress->getEmail()) {
                if ($billingAddress->getEmail() == $postOrder['email']) {
                    $dataList['customer_email'] = $postOrder['email'];
                } else {
                    $this->messageManager->addErrorMessage(__('your email doesnot match with order email please try again'));
                    return false;
                }

            }
            $dataList['customer_id'] = $order->getCustomerId();
            $dataList['customer_name'] = $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname();
        } else {
            $dataList['customer_email'] = $order->getCustomerEmail();
            $dataList['customer_name'] = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        }
        return $dataList;
    }

    /**
     * @param $item
     * @param $orderId
     * @return array|bool
     */
    public function getItemsList($item, $orderId)
    {
        if ($this->rmaConfigHelper->isProductShippedEnabled()) {
            $created_date = '';
            $shipmentItems = $this->shipmentItemFactory->create()->addFieldToFilter('order_item_id', $item->getId());
            if (!empty ($shipmentItems)) {
                foreach ($shipmentItems as $shipItem) {
                    $shipments = $this->shipmentCollectionFactory->create()->addFieldToFilter('entity_id', $shipItem->getParentId());
                    foreach ($shipments as $shipment) {
                        $created_date = $shipment->getCreatedAt();
                    }
                    $now = date_create($this->dateTime->gmtDate());
                    $created = date_create($created_date);
                    $diff = date_diff($created, $now);
                    if ($diff->format("%R%a") > $this->rmaConfigHelper->getMinDaysAfter()) {
                        return false;
                    }
                }
            }
        }

        $max = 0; // no of qty invoice
        $orig_quantity = $item->getQtyOrdered();
        $itemlist = array(
            'product_id' => $item->getProductId(),
            'item_id' => $item->getId(),
            'sku' => $item->getSku(),
            'name' => $item->getName(),
            'price' => $item->getPrice(),
            'product_type' => $item->getData('product_type'),
            'row_total_incl_tax' => $item->getData('row_total') + $item->getData('tax_amount') - $item->getData('discount_amount'),
            'vendor_id' => $item->getVendorId() ? $item->getVendorId() : 0
        );

        $product_filter = $this->rmaConfigHelper->ProductFilterOutStatus(); // status for product
        if ($item->getData('product_type') == 'bundle') {
            if ($item->getChildrenItems()) {
                foreach ($item->getChildrenItems() as $childrenItem) {
                    if ($this->rmaConfigHelper->isOrderCanceledEnabled()) {
                        $max = $childrenItem->getQtyOrdered();
                    } elseif ($this->rmaConfigHelper->isProductInvoicedEnabled()) {
                        $max = $childrenItem->getQtyInvoiced();
                    } elseif ($this->rmaConfigHelper->isProductShippedEnabled()) {
                        $max = $childrenItem->getQtyShipped();
                    } else {
                        $max = $childrenItem->getQtyInvoiced();
                    }
                    $childrenMax = $max - $childrenItem->getQtyRefunded();
                    $qty_to_cancel = $childrenItem->getQtyToCancel();
                    $qty_to_refund = $childrenItem->getQtyInvoiced();
                    $childrenAllItems = $this->getAllRmaItems($orderId, $item->getSku());
                    if (isset ($childrenAllItems [$childrenItem->getSku()])) {
                        foreach ($childrenAllItems [$childrenItem->getSku()] as $value) {
                            $childrenMax = $childrenMax - $value ['count'];
                        }
                    }
                    $max += $childrenMax;
                }
            }
        } else {
            if ($this->rmaConfigHelper->isProductInvoicedEnabled()) {
                $max = $item->getQtyInvoiced();
            } elseif ($this->rmaConfigHelper->isProductShippedEnabled()) {
                $max = $item->getQtyShipped();
            } else {
                $max = $item->getQtyInvoiced();
            }

            $max = floatval($max) - floatval($item->getQtyRefunded());
            $allItems = $this->getAllRmaItems($orderId, $item->getSku());

            if (isset ($allItems [$item->getSku()])) {
                foreach ($allItems [$item->getSku()] as $value) {
                    $max = floatval($max) - floatval($value ['rma_qty']);
                }
            }
        }

        $itemlist ['qty'] = max($max, 0);
        if (isset ($itemlist ['qty']) && floatval($itemlist ['qty']) == 0) {
            return false;
        }
        if ($orig_quantity != $itemlist ['qty']) {
            $itemlist ['row_total_incl_tax'] = ($itemlist ['row_total_incl_tax'] * $itemlist ['qty']) / $orig_quantity;
        }
        return $itemlist;
    }

    /**
     * retrieve ordered item's details
     *
     * @return array
     */
    public function getAllRmaItems($orderId, $itemSku, $onlyActiveRma = true)
    {

        $orderRmaCollection = $this->rmaRequestFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId);
        $allRmaItems = [];

        foreach ($orderRmaCollection as $rma) {

            if ($rma->getStatus() != 'Approved') {
                $rmaItems = $this->rmaItemFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('rma_request_id', $rma->getRmaRequestId())
                    ->addFieldToFilter('sku', $itemSku);

                foreach ($rmaItems as $items) {
                    if ($items->getSku() == $itemSku) {
                        $allRmaItems[$items->getSku()][] = [
                            'rma_qty' => $items->getRmaQty(),
                            'rma_id' => $rma->getRmaId(),
                        ];
                    }

                }
            }
        }

        return $allRmaItems;
    }

    /**
     * validate order for RMA permissable
     * @param $orderId
     * @return bool
     */
    public function isValidOrder($orderId)
    {
        $order = $this->salesOrderFactory->create()->load($orderId, 'increment_id');

        if (!($order->getData() == array())) {
            //Gets order items from post if per-order item RMA is allowed
            //and gets it directly from order otherwise
            $_orderItems = array();
            foreach ($order->getAllVisibleItems() as $_item) {

                if ($_item && $_item->getProductType() == "virtual")
                    continue;
                if ($this->getItemsList($_item, $orderId)) {

                    $_orderItems[$_item->getId()] = $this->getItemsList($_item, $orderId);
                } else {
                    continue;
                }
            }

            if (empty($_orderItems)) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
        return false;

    }


    /**
     * @param $order
     * @return bool
     */
    public function canReOrder($order)
    {
        $flag = false;
        foreach ($order->getAllVisibleItems() as $_item) {
            if ($_item->getProduct()->getSku() == "wallet_product") {
                $flag = true;
                break;
            }
        }
        if ($flag) {
            return false;
        }
        return true;
    }

    /**
     * @param $rmaRequestId
     * @param $rmadata
     * @return array
     */
    public function generateCreditMemoForRma($rmaRequestId, $rmadata)
    {

        $error_array = [];
        try {
            $rmaCollection = $this->rmaRequestFactory->create()->load($rmaRequestId);
            $order = $this->salesOrderFactory->create()->loadByIncrementId($rmaCollection->getOrderId());
            $rmaItems = $this->rmaItemFactory->create()
                ->getCollection()
                ->addFieldToFilter('rma_request_id', $rmaRequestId);
            if ($rmadata['resolution_requested'] == 'Refund') {
                if (!$order->canCreditmemo()) {

                    $this->messageManager->addErrorMessage('Can Not create CreditMemo');
                }
            }
            if ($rmadata['resolution_requested'] == 'Cancel') {
                if ($this->rmaConfigHelper->isOrderCanceledEnabled()) {
                    if (!$order->canCancel()) {
                        $this->messageManager->addErrorMessage('Can Not create CreditMemo');
                    }
                }
            }
            $data = [];
            $data['order_id'] = $order->getId();
            $data['creditmemo'] = ['items' => []];
            $invoiceId = '';

            foreach ($rmaItems as $item) {

                $qty = $this->rmaItemFactory->create()->convertQty($item->getRmaQty());
                if ($item->getSalesItemId()) {
                    $orderItem = $this->itemFactory->create()->load($item->getSalesItemId());
                } else {
                    $orderItem = $this->orderItemCollectionFactory->create()
                        ->addFieldToFilter('order_id', $order->getId())
                        ->addFieldToFilter('sku', $item->getSku())->getFirstItem();
                }
                /*when it is refunded */
                if ($rmadata['resolution_requested'] == 'Refund') {
                    if ($orderItem->getQtyInvoiced()) {
                        $invoiceItems = $this->invoiceItemFactory->create()->getCollection()
                            ->addFieldToFilter('order_item_id', $orderItem->getItemId());
                        foreach ($invoiceItems as $items) {
                            $invoiceId = $items->getParentId();
                        }
                    }

                    $data['creditmemo']['items'][$orderItem->getId()] = ['qty' => $qty];
                }


                if (($rmadata['resolution_requested'] == 'Cancel') && ($this->rmaConfigHelper->isOrderCanceledEnabled())) {
                    if ((floatval($orderItem->getQtyInvoiced()) == 0) && ($orderItem->getQtyToCancel() >= $qty)) {
                        $this->eventManager->dispatch('csrma_order_item_cancel', ['item' => $orderItem]);
                        $orderItem->setQtyCanceled($item->getRmaQty())->save();
                        $order->save();

                        $data['creditmemo']['items'][$orderItem->getId()] = ['qty' => $qty];
                    } else {

                        $this->eventManager->dispatch('csrma_order_item_cancel', ['item' => $orderItem]);
                        $orderItem->setQtyCanceled($orderItem->getQtyToCancel())->save();
                        $order->save();
                        $refund_qty = $qty - $orderItem->getQtyToCancel();

                        if (floatval($orderItem->getQtyInvoiced()) >= $refund_qty) {
                            $data['creditmemo']['items'][$orderItem->getId()] = ['qty' => $refund_qty];
                        } else {
                            $data['creditmemo']['items'][$orderItem->getId()] = ['qty' => floatval($orderItem->getQtyInvoiced())];
                        }

                    }
                }
            }
            $itemsArr = [];
            foreach ($data['creditmemo']['items'] as $k => $v) {
                $salesItem = $this->itemFactory->create()->load($k);
                if ($salesItem->getProductType() == "bundle") {
                    $childItems = $this->orderItemCollectionFactory->create()->addFieldToFilter('parent_item_id', $k);
                    foreach ($childItems as $k1 => $v1) {
                        $itemsArr = ['qty' => $v1->getQtyOrdered()];
                        $data['creditmemo']['items'][$v1->getItemId()] = $itemsArr;

                    }
                    unset($data['creditmemo']['items'][$k]);
                }
            }
            $adjustPositive = 0;
            $adjustNegative = 0;
            if (isset($rmadata['additional_refund']) && $rmadata['additional_refund'] >= 0) {
                $adjustPositive = 0;
                $adjustNegative = $rmadata['additional_refund'];
                $comment_text = 'Added';

            } else {
                $adjustNegative = 0;
                $adjustPositive = 0;
                $comment_text = 'Cancel';
            }
            $data['creditmemo']['shipping_amount'] = 0;
            $data['creditmemo']['comment_text'] = $comment_text;
            $data['creditmemo']['adjustment_positive'] = $adjustPositive;
            $data['creditmemo']['adjustment_negative'] = $adjustNegative;

            $this->creditmemoLoader->setOrderId($data['order_id']);

            $order = $this->salesOrderFactory->create()->load($data['order_id']);
            $creditMemoItems = [];
            $items = $data['creditmemo'];

            $this->creditmemoLoader->setCreditmemoId('');
            $this->creditmemoLoader->setCreditmemo($items);
            $this->creditmemoLoader->setInvoiceId('');
            try {
                $creditmemo = $this->creditmemoLoader->load();

                $request = $this->requestInterface;
                $request->setParam('creditmemo', $items);


                if ($creditmemo) {

                    $error = [];
                    if (!$creditmemo->isValidGrandTotal()) {
                        $error[] = 'The credit memo\'s total must be positive.';

                    }

                    $creditmemoManagement = $this->creditmemoManagement;
                    $data = [];
                    $data['do_offline'] = 0;
                    $data['send_email'] = 1;
                    $creditmemoManagement->refund($creditmemo, (bool)$data['do_offline'], !empty($data['send_email']));

                    if (!empty($data['send_email'])) {
                        \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Sales\Model\Order\Email\Sender\CreditmemoSender')->send($creditmemo);
                    }
                }

            } catch (\Exception $e) {

                $error_array['error'] = $e->getMessage();
            }


        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error_array['error'] = $e->getMessage();

        } catch (\Exception $e) {
            $this->logger->critical($e);
            $error_array['error'] = $e->getMessage();
        }
        return $error_array;
    }

    /**
     * Retrieve RMA Lable text
     * @param $orderId
     * @return bool
     */
    public function getRmaLabel($orderId)
    {
        $filter = $this->rmaConfigHelper->OrderFilterStatus();
        $_ordersPlaced = $this->salesordercollectionFactory->create()
            ->addFieldToFilter('status', array('in' => $filter))
            ->addFieldToFilter('entity_id', $orderId);
        $_ordersPlaced->getSelect()
            ->where('updated_at > DATE_SUB(NOW(), INTERVAL ? DAY)', $this->rmaConfigHelper->getMinDaysAfter());
        $_ordersPlaced->load();
        if (count($_ordersPlaced->getData()) > 0) {
            $_selectedOrderId = $_ordersPlaced->getData();
            if (isset($_selectedOrderId[0]['increment_id']) && $this->isValidOrder($_selectedOrderId[0]['increment_id'])) {
                return $this->isValidOrder($_selectedOrderId[0]['increment_id']);
            }
        }
    }

    /**
     * @param $orderId
     * @return array
     */
    public function getAvailableResolutionsForOrder($orderId)
    {
        $resolution_list = $this->rmaConfigHelper->getResolution();
        $order = $this->salesOrderFactory->create()->load($orderId);
        if (!$this->rmaConfigHelper->isOrderCanceledEnabled()
            || !$order->canInvoice() && $order->canCancel()) {
            unset($resolution_list['Cancel']);
        }
        if (!$order->getInvoiceCollection()->getData()
            || empty($order->getInvoiceCollection()->getData())) {
            unset($resolution_list['Refund']);
        }
        if (!$order->getShipmentsCollection()->getData()
            || empty($order->getShipmentsCollection()->getData())) {
            unset($resolution_list['Repair']);
        }
        if ($order->getShipmentsCollection()->getData()
            && !$order->canShip()) {
            unset($resolution_list['Cancel']);
        }
        return $resolution_list;
    }
}
