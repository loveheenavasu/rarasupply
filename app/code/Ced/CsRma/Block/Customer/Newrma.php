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

namespace Ced\CsRma\Block\Customer;

/**
 * Class Newrma
 * @package Ced\CsRma\Block\Customer
 */
class Newrma extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Ced\Rma\Helper\Config
     */
    public $rmaConfigHelper;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    public $salesOrderFactory;

    /**
     * @var \Ced\CsRma\Helper\Data
     */
    public $rmaDataHelper;

    /**
     * @var \Ced\CsRma\Helper\OrderDetail
     */
    public $rmaOrderHelper;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * Newrma constructor.
     * @param \Ced\CsRma\Helper\Config $rmaConfigHelper
     * @param \Ced\CsRma\Helper\Data $rmaDataHelper
     * @param \Magento\Framework\Session\Generic $generic
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Ced\CsRma\Helper\OrderDetail $rmaOrderHelper
     * @param array $data
     */
    public function __construct(
        \Ced\CsRma\Helper\Config $rmaConfigHelper,
        \Ced\CsRma\Helper\Data $rmaDataHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Ced\CsRma\Helper\OrderDetail $rmaOrderHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        array $data = []
    )
    {
        $this->priceHelper = $priceHelper;
        $this->rmaOrderHelper = $rmaOrderHelper;
        $this->generic = $generic;
        $this->rmaDataHelper = $rmaDataHelper;
        $this->orderFactory = $orderFactory;
        $this->rmaConfigHelper = $rmaConfigHelper;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->currentCustomer = $currentCustomer;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->getTitle());
        return parent::_prepareLayout();
    }

    /**
     * Return the title, either editing an existing address, or adding a new one.
     *
     * @return string
     */
    public function getTitle()
    {
        if ($title = $this->getData('title')) {
            return $title;
        }
        $title = __('Request New RMA');
        return $title;
    }

    /**
     * Return the Url to go back.
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }
        return $this->getUrl('customer/account/');
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'csrma/customerrma/save',
            ['_secure' => true]
        );
    }

    /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->currentCustomer->getCustomer();
    }

    /**
     * Return the customer orders.
     *
     * @return string
     */
    public function getCustomerOrders()
    {

        $i = 0;
        $return_array = [];
        $filter = $this->rmaConfigHelper->OrderFilterStatus();

        $order_selected = $this->salesOrderFactory->create()
            ->addFieldToFilter('customer_id', $this->getCustomer()->getId())
            ->addFieldToFilter('status', array('in' => $filter))
            ->setOrder('created_at', 'desc');
        $order_selected->getSelect()
            ->where('updated_at > DATE_SUB(NOW(), INTERVAL ? DAY)',
                $this->rmaConfigHelper->getMinDaysAfter());
        $order_selected->load();
        $keys = [];
        if (count($order_selected) > 0) {
            foreach ($order_selected->getData() as $key => $order) {

                $return_array = $order_selected->getData();
                $validOrder = $this->rmaOrderHelper->isValidOrder($order['increment_id']);

                if (!$validOrder) {
                    $keys[] = $key;
                }

            }
            for ($i = 0; $i < count($keys); $i++) {

                unset($return_array[$keys[$i]]);

            }
            $return_array = array_values($return_array);
            return $return_array;
        } else {
            $this->generic->setError('Cannot Create RMA for given Order');
            return false;
        }
    }

    /**
     * Return the customer order's items.
     *
     * @return string
     */
    public function getCustomerOrdersItems($incrementId)
    {
        $_order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if ($_order && $_order->getId()) {
            $_orderItems = [];
            foreach ($_order->getAllVisibleItems() as $_item) {
                if (in_array($_item->getProductType(), ['virtual'])) {
                    continue;
                }
                if ($this->getRequest()->getParam('item_id')) {
                    if ($_item->getId() == $this->getRequest()->getParam('item_id')) {
                        $list = $this->rmaOrderHelper->getItemsList($_item, $incrementId);
                        if ($list && is_array($list)) {
                            $_orderItems[] = $list;
                        }
                    }
                } else {
                    $list = $this->rmaOrderHelper->getItemsList($_item, $incrementId);
                    if ($list && is_array($list)) {
                        $_orderItems[] = $list;
                    }
                }
            }

            return $_orderItems;
        } else {
            return array($this->__("no data available"));
        }
    }

    /**
     * Return the order's item.
     *
     * @return string
     */
    public function getResolutionForOrder($incrementId)
    {
        $resolution_def_array = array('' => 'Please select a resolution');
        $resolutions_updated = $this->getResolutionsForOrder($incrementId);
        $resolutions = array_merge($resolution_def_array, $resolutions_updated);

        return $resolutions;
    }

    /**
     * @param $incrementId
     * @return array
     */
    protected function getResolutionsForOrder($incrementId)
    {
        $resolution_list = $this->rmaConfigHelper->getResolution();
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
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

    /**
     * @param $incrementId
     * @return array
     */
    public function getOrderData($incrementId)
    {
        return $this->rmaDataHelper->getOrderCollection($incrementId);
    }
}
