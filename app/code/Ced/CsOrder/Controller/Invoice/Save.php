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

namespace Ced\CsOrder\Controller\Invoice;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Framework\Exception\LocalizedException;
use Ced\CsOrder\Model\InvoiceFactory;

/**
 * Class Save
 * @package Ced\CsOrder\Controller\Invoice
 */
class Save extends \Ced\CsMarketplace\Controller\Vendor
{

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Ced\CsMarketplace\Model\VordersFactory
     */
    protected $vordersFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    protected $invoice;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    /**
     * Save constructor.
     * @param InvoiceService $invoiceService
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param ShipmentFactory $shipmentFactory
     * @param \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        InvoiceFactory $invoice,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\DB\Transaction $transaction,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    ) {
        $this->invoiceSender = $invoiceSender;
        $this->registry = $registry;
        $this->shipmentFactory = $shipmentFactory;
        $this->invoiceService = $invoiceService;
        $this->shipmentSender = $shipmentSender;
        $this->vordersFactory = $vordersFactory;
        $this->backendSession = $backendSession;
        $this->orderFactory = $orderFactory;
        $this->invoice = $invoice;
        $this->logger = $logger;
        $this->transaction = $transaction;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * Prepare shipment
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _prepareShipment($invoice)
    {

        $invoiceData = $this->getRequest()->getParam('invoice');

        $shipment = $this->shipmentFactory->create(
            $invoice->getOrder(),
            isset($invoiceData['items']) ? $invoiceData['items'] : [],
            $this->getRequest()->getPost('tracking')
        );

        if (!$shipment->getTotalQty()) {
            return false;
        }

        return $shipment->register();
    }

    /**
     * Save invoice
     * We can save only new invoice. Existing invoices are not editable
     *
     * @return \Magento\Framework\Controller\ResultInterface
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        /**
         * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();

        $formKeyIsValid = true;
        $isPost = $this->getRequest()->isPost();

        if (!$formKeyIsValid || !$isPost) {

            $this->messageManager->addErrorMessage(__('We can\'t save the invoice right now.'));
            return $resultRedirect->setPath('csorder/vorders/index');
        }

        $data = $this->getRequest()->getPost('invoice');
        $vorderId = $this->getRequest()->getParam('order_id');
        $vorder = $this->vordersFactory->create()->load($vorderId);
        $oorder = $vorder->getOrder();
        $this->registry->register("current_order", $oorder);
        $shipping_amount = $vorder->getShippingAmount();
        $orderId = $vorder->getOrder()->getId();

        if (!empty($data['comment_text'])) {
            $this->backendSession->setCommentText($data['comment_text']);
        }

        try {
            $invoiceData = $this->getRequest()->getParam('invoice', []);

            $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];
            $invoiceItems = array_filter($invoiceItems);
            $order = $this->orderFactory->create()->load($orderId);
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
            }

            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }

            $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);

            if (!$invoice) {
                throw new LocalizedException(__('We can\'t save the invoice right now.'));
            }

            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
            $TotalVorders = $this->vordersFactory->create()->getCollection()
                ->addFieldToFilter('order_id',$order->getIncrementId());
            if(count($TotalVorders->getData())>1){
                $vInvoice = $this->invoice->create()->getCollection()
                    ->addFieldToFilter('vendor_id',$vorder->getVendorId())
                    ->addFieldToFilter('invoice_order_id',$order->getEntityId());
                if(!empty($vInvoice->getData())){
                    $shipping_amount = 0;
                    $grandTotal = $invoice->getGrandTotal();
                }else{
                    $grandTotal = $vorder->getOrderTotal();
                }
                $invoice->setShippingAmount($shipping_amount);
                $invoice->setBaseShippingAmount($shipping_amount);
                $invoice->setGrandTotal($grandTotal+$shipping_amount);
                $invoice->setBaseGrandTotal($grandTotal+$shipping_amount);
            }
            $this->registry->register('current_invoice', $invoice);
            if (!empty($data['capture_case'])) {
                $invoice->setRequestedCaptureCase($data['capture_case']);
            }

            if (!empty($data['comment_text'])) {
                $invoice->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );

                $invoice->setCustomerNote($data['comment_text']);
                $invoice->setCustomerNoteNotify(isset($data['comment_customer_notify']));
            }

            $invoice->register();

            $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $invoice->getOrder()->setIsInProcess(true);

            $transactionSave = $this->transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $shipment = false;
            if (!empty($data['do_shipment']) || (int)$invoice->getOrder()->getForcedShipmentWithInvoice()) {
                $shipment = $this->_prepareShipment($invoice);
                if ($shipment) {
                    $transactionSave->addObject($shipment);
                }
            }

            $transactionSave->save();
            if($order->getPayment()->getMethodInstance()->getCode() == 'ccavenuepay')
            {
                if ($order->getStatus() == 'ccpending' || $order->getStatus() == 'pending') {
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
                    $order->save();
                }
            }
            try {
                $this->invoiceSender->send($invoice);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }
            if (isset($shippingResponse) && $shippingResponse->hasErrors()) {
                $this->messageManager->addErrorMessage(
                    __(
                        'The invoice and the shipment  have been created. ' .
                        'The shipping label cannot be created now.'
                    )
                );
            } elseif (!empty($data['do_shipment'])) {
                $this->messageManager->addSuccessMessage(__('You created the invoice and shipment.'));
            } else {
                $this->messageManager->addSuccessMessage(__('The invoice has been created.'));
            }

            if($order->getPayment()->getMethodInstance()->getCode() == 'ccavenuepay'){
                $invoiceVendor = [];

                foreach($order->getAllVisibleItems() as $item){
                    if($item->getVendorId() == $vorder->getVendorId()){
                        if($item->getId() !=''){
                            $invoiceVendor[$item->getVendorId()] = $item->getVendorId();
                        }
                    }
                }
                foreach($invoiceVendor as $vendorId){
                    $this->vinvoice = $this->invoice->create();
                    $vInvoice = $this->vinvoice;

                    try{
                        $id = $invoice->getId();
                        $vInvoice->setInvoiceId($id);
                        $vInvoice->setVendorId($vendorId);
                        $vInvoice->setInvoiceOrderId($invoice->getOrderId());
                        if($vInvoice->canInvoiceIncludeShipment($invoice)) {
                            if($vorder = $this->vordersFactory->create()->getVorderByInvoice($invoice)) {
                                $vInvoice->setShippingCode($vorder->getCode());
                                $vInvoice->setShippingDescription($vorder->getShippingDescription());
                                $vInvoice->setBaseShippingAmount($vorder->getBaseShippingAmount());
                                $vInvoice->setShippingAmount($vorder->getShippingAmount());
                            }
                        }
                        $vInvoice->save();
                    }catch(\Exception $e){
                        $e->getMessage();
                    }
                }
            }

            if ($shipment) {
                try {
                    if (!empty($data['send_email'])) {
                        $this->shipmentSender->send($shipment);
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    $this->messageManager->addErrorMessage(__('We can\'t send the shipment right now.'));
                }
            }
            $this->backendSession->getCommentText(true);
            return $resultRedirect->setPath('csorder/vorders/view', ['order_id' => $vorderId]);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t save the invoice right now.'));
            $this->logger->critical($e);
        }
        return $resultRedirect->setPath('csorder/*/new', ['order_id' => $vorderId]);
    }
}

