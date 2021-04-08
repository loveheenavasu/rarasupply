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
namespace Ced\CsRma\Helper;

use Magento\Framework\App as App;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Guest extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Ced\CsRma\Helper\Config
     */
    protected  $rmaConfigHelper;
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;
    
    
    
    /**
     * @param App\Helper\Context $context
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Ced\Rma\Helper\Config $rmaConfigHelper
     */

    public function __construct(
        App\Helper\Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Ced\CsRma\Helper\Config $rmaConfigHelper,
        \Ced\CsRma\Helper\OrderDetail $rmaOrderHelper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\Config\ScopeConfigInterface $ScopeConfigInterface
    ) {
       
        $this->resultForwardFactory = $resultForwardFactory;
        $this->coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->orderFactory = $orderFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->rmaConfigHelper = $rmaConfigHelper;
        $this->rmaOrderHelper = $rmaOrderHelper;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->scopeConfigInterface = $ScopeConfigInterface;
        $this->generic = $generic;
        parent::__construct($context);
    }

    /**
     * Try to load valid order
     *
     * @param App\RequestInterface $request
     * @return \Magento\Framework\Controller\Result\Redirect|bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function loadValidOrder(App\RequestInterface $request)
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('csrma/customerrma/index');
        }

        $post = $request->getPostValue();
        $errors = false;

        $order = $this->orderFactory->create();

        //$fromCookie = $this->cookieManager->getCookie(self::COOKIE_NAME);

        if (empty($post)) {
            echo json_encode(['status'=>false]);
            exit();

        } elseif (!empty($post) && isset($post['data'][1]['value']) && isset($post['data'][3]['value'])) {
            $incrementId = $post['data'][1]['value'];
            $lastName = $post['data'][2]['value'];
            $email = $post['data'][3]['value'];
            $storeId = $this->_storeManager->getStore()->getId();
            

            if (empty($incrementId) || empty($lastName) || empty($storeId) || empty($email)) {
                $errors = true;
            }

            if($incrementId){
                
                $order_status = $this->orderFactory->create()->loadByIncrementId($incrementId)->getStatus();
                $allowInvoice = $this->scopeConfigInterface->getValue('ced_csmarketplace/rma_general_group/product_invoiced');
                $allowShipped = $this->scopeConfigInterface->getValue('ced_csmarketplace/rma_general_group/product_shipped');
               
                if($allowInvoice || $allowShipped ){
                    if($order_status == 'pending'){
                        $msg = 'You cannot request RMA for pending orders';
                        $this->messageManager->addError(__('You cannot request RMA for pending orders'));
                        echo json_encode(['status'=>false,'msg'=>$msg]);
                        exit();
                    }
                }

                $check = false;
                if($this->getCustomerOrders()) {
                    foreach ($this->getCustomerOrders() as $items) {
                        if ($items['increment_id'] == $incrementId) {
                            $check = true;
                        }
                    }
                }
                else{
                    $msg = 'Incorrect details';
                    $this->messageManager->addErrorMessage("Incorrect details");
                    echo json_encode(['status'=>false,'msg'=>$msg]);
                    exit();
                }

                if($check == false){
                    $msg = 'You have already requested RMA for this order';
                        $this->messageManager->addError(__('You have already requested RMA for this order'));
                        echo json_encode(['status'=>false,'msg'=>$msg]);
                        exit();
                }

            }
            if (!$errors) {
                $order = $order->loadByIncrementIdAndStoreId($incrementId, $storeId);
            }

            if ($order->getId() &&  !$order->canInvoice() ) 
            {
                $billingAddress = $order->getBillingAddress();
                if (strtolower($lastName) == strtolower($billingAddress->getLastname()) &&
                    (strtolower($email) == strtolower($billingAddress->getEmail()))) 
                {
                    $errors = false;
                }
            }
           
        } 
        if (!$errors && $order->getId()) {
            $billingAddress = $order->getBillingAddress();
            $this->coreRegistry->register('rma_current_order', $order);
            echo json_encode(['status'=>true,'order_id'=>$incrementId,'email'=>$billingAddress->getEmail()]);
            exit();

        }

        $msg = 'You entered incorrect data. Please try again.';
        $this->messageManager->addError(__('You entered incorrect data. Please try again.'));
        echo json_encode(['status'=>false,'msg'=>$msg]);
        exit();
    }
    /**
     * Get Breadcrumbs for current controller action
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @return void
     */
    public function getBreadcrumbs(\Magento\Framework\View\Result\Page $resultPage)
    {
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()
            ]
        );
        $breadcrumbs->addCrumb(
            'cms_page',
            ['label' => __('RMA Information'), 'title' => __('RMA Information')]
        );
    }

    /**
     * Get guest order item for current order id
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @return void
     */
        public function getGuestOrdersItems(App\RequestInterface $request)
    {
        $post = $request->getPostValue();
        $htmlcontent = '';
        $_order = $this->orderFactory->create()->loadByIncrementId($post['order_id']);
        if (is_array($_order->getData())) {
             $htmlcontent .= '<input type="hidden" value="'.$post['order_id'].'" name="order_id" id="guest_increment_id">';
            foreach ($_order->getAllVisibleItems() as $_item) {
                $htmlcontent .= '<tr class="item-list">';
                $htmlcontent .= '<td>'.$_item->getSku().'</td>';
                $htmlcontent .= '<td>'.$_item->getName().'</td>';
                $htmlcontent .= '<td>'.intval($_item->getPrice()).'</td>';
                $htmlcontent .= '<td>'.intval($_item->getQtyOrdered()).'</td>';
                $htmlcontent .= '<td>';
                $htmlcontent .= '<input type="hidden" value="'.$_item->getProductId().'" name="item-data[item-id][]">';
                $htmlcontent .= '<input type="hidden" value="'.$_item->getSku().'" name="item-data[item-sku][]">';
                $htmlcontent .= '<input  type="hidden" value="'.$_item->getName().'" name="item-data[item-name][]">';
                $htmlcontent .= '<input  type="hidden" value="'.$_item->getPrice().'" name="item-data[item-price][]">';
                $htmlcontent .= '<input  type="hidden" value="'.$_item->getId().'" name="item-data[order-item-id][]">';
                $htmlcontent .= '<input type="hidden" value="'.$_item->getQtyOrdered().'" name="item-data[item-qty][]">';
                $htmlcontent .= '<input type="text"  value="'.intval($_item->getQtyOrdered()).'" id="rma-qty" name="item-data[rma-qty][]" class="input-text qty-input" oninput="validateGuestQty(this,'.intval($_item->getQtyOrdered()).')">';
                $htmlcontent .='</td>';
                $htmlcontent .= '</tr>';
            }  
            echo $htmlcontent;
        } else {
            echo __("no data available");
        }
    }

    /**
     * Return the customer orders.
     *
     * @return string
     */
    public function getCustomerOrders()
    {   
        
        $i=0;$return_array =[];
        $filter = $this->rmaConfigHelper->guestOrderFilterStatus();

        $order_selected = $this->salesOrderFactory->create()
                    ->addFieldToFilter('customer_id', array('null' => true))
                    ->addFieldToFilter('status', array('in' => $filter))
                    ->setOrder('created_at','desc');
                    
        $order_selected->getSelect()
                    ->where('updated_at > DATE_SUB(NOW(), INTERVAL ? DAY)',
                    $this->rmaConfigHelper->getMinDaysAfter());
        $order_selected->load();
        $keys = [];
        
        if(count($order_selected) > 0 ) {
            foreach($order_selected->getData() as $key=>$order){
              
                $return_array =$order_selected->getData();
                $validOrder = $this->rmaOrderHelper->isValidOrder($order['increment_id']);
              
                if(!$validOrder){
                    $keys[] = $key;
                 }
                
            }
            for($i=0;$i<count($keys);$i++){

              unset($return_array[$keys[$i]]);

            }
            $return_array = array_values($return_array);
            return $return_array;
        } else {
            $this->generic->setError('Cannot Create RMA for given Order');
            return false;
        }
    }

}
