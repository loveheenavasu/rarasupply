<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */ 
namespace Ced\CsRma\Model;

/**
 * Rma Request
 */
class Request extends \Magento\Framework\Model\AbstractModel
{

    /**
    * @var Ced\Rma\Model\RmanotificationFactory
    */
    protected $rmaNotificationFactory;
    /**
     * @var \Ced\Rma\Model\RmaitemsFactory
     */
    protected $rmaItemFactory;
    /**
     * @var \Ced\Rma\Model\RmashippingFactory
     */
    protected $rmaShippingFactory;
    
    /**
    * @var \Magento\Sales\Model\OrderFactory 
    */
    protected $salesOrderFactory;

    /**
    * @var \Magento\Framework\Stdlib\DateTime\DateTime
    */
    protected $dateTime;

    /**
    * @var \Ced\Rma\Helper\Data
    */
    protected $rmaDataHelper;

    const PENDING_STATUS = "Pending";

    const OWNER_ADMIN = 1;

    const OWNER_VENDOR = 2;

    const OWNER_CUSTOMER = 0;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Ced\Rma\Model\Resource\Rmaitems\CollectionFactory
     */
    protected $rmaResourceRmaitemsCollectionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Ced\Rma\Model\RmaitemsFactory $rmaItemFactory
     * @param \Ced\Rma\Model\RmanotificationFactory $rmaNotificationFactory
     * @param \Ced\Rma\Model\RmashippingFactory $rmaShippingFactory
     * @param \Ced\Rma\Helper\Data $rmaDataHelper
     * @param \Magento\Sales\Model\OrderFactory $salesOrderFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param  \Magento\Framework\App\Request\Http $requestHttp
     */

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\CsRma\Model\RmaitemsFactory $rmaItemFactory,
        \Ced\CsRma\Model\RmanotificationFactory $rmaNotificationFactory,
        \Ced\CsRma\Model\RmashippingFactory $rmaShippingFactory,
        \Ced\CsRma\Helper\Data $rmaDataHelper,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\Request\Http $requestHttp
    ) {
        $this->rmaDataHelper = $rmaDataHelper;
        $this->rmaNotificationFactory = $rmaNotificationFactory;
        $this->rmaItemFactory = $rmaItemFactory;
        $this->rmaShippingFactory = $rmaShippingFactory;
        $this->dateTime = $dateTime;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->storeManager = $storeManager;
        $this->backendHelper = $backendHelper;
        $this->_request = $requestHttp;
        parent::__construct(
            $context,
            $registry
        );
    }

	/**
     * Initialize resource mode
     *
     * @return void
     */
    protected function _construct()
    {
    	parent::_construct();
        $this->_init('Ced\CsRma\Model\ResourceModel\Request');
    }

    /**
     * Create db structure
     *
     * @return $this
     */
    public function afterSave()
    {   
        $items = $this->getItem();
        $orderId = $this->_request->getParam('order_id'); 
        $id = $this->getId();
        $vendorId = $this->getVendorId();
        $status = $this->getStatus();
        if(isset($items)){
            if(is_array($items) && !empty($items)) {
                $itemCollection = $this->saveRmaItemDetail($items,$id);
            }
        }
        
        $notificationCollection = $this->saveNotificationData($status,$id,$vendorId);
        if(isset($orderId) && $orderId!=null){
             $shippingCollection = $this->saveShippingData($orderId,$id);
        }
        return parent::afterSave();
    }

    /**
     * save RmaItem Detail
     *
     * @return $this
     */

    protected function saveRmaItemDetail($item,$id)
    {
        $itemData =[];
        foreach ($item as $key =>$value) {
            $itemModel = $this->rmaItemFactory->create();
            $itemModel->addData($value)
                    ->setData('rma_request_id',$id)
                    ->save();
        }
    }

    /**
     * save Notification Data
     *
     * @return $this
     */
    protected function saveNotificationData($status,$id,$vendorId)
    {
        $notificationModel = $this->rmaNotificationFactory->create();
        $message = $this->rmaDataHelper->getStatusData($status);
        $data = ['rma_request_id'=> $id,
                'created_at'=> $this->dateTime->gmtDate(),
                'notification_message'=> $message
            ];
        $notificationModel->setData($data);

        if ($vendorId) {
            $notificationModel->setData('owner',self::OWNER_VENDOR);
        } else {
            $notificationModel->setData('owner',self::OWNER_ADMIN);
        }
        $notificationModel->save();
    }

    /**
     * save Shipping Data
     *
     * @return $this
     */

    protected function saveShippingData($orderId,$id)
    {
        $shippingModel = $this->rmaShippingFactory->create();
        $shipData = [];
        $order = $this->salesOrderFactory->create()->loadByIncrementId($orderId);
    
        $shipData['rma_request_id'] = $id;
        $shipData['customer_firstname'] = $order->getBillingAddress()->getFirstname();
        $shipData['customer_lastname'] = $order->getBillingAddress()->getLastname();
        $shipData['customer_middlename'] = $order->getBillingAddress()->getMiddlename();
        $shipData['address'] = $order->getBillingAddress()->getData('street');
        $shipData['dest_country_id'] = $order->getBillingAddress()->getCOuntryId();
        $shipData['dest_region'] = $order->getBillingAddress()->getRegion();
        $shipData['dest_zip'] = $order->getBillingAddress()->getPostCode();
        $shippingModel->setData($shipData)
            ->setUpdatedAt($this->dateTime->gmtDate())
            ->save();
    }
}
