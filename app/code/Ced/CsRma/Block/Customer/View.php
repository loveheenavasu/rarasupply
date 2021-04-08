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
namespace Ced\CsRma\Block\Customer;

use Magento\Customer\Model\Context;

class View extends \Magento\Framework\View\Element\Template
{
     /**
    * @var Magento\Sales\Model\OrderFactory
    */
    public $orderAddressRepository;

     /**
     * @var \Ced\CsRma\Helper\Data $rmaDataHelper
     */
    
    public $rmaDataHelper;
    
    /**
     * @var \Ced\CsRma\Model\RmaitemsFactory
     */

    public $rmaItemFactory;
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    
    protected $_customerSession;

    /**
     * @var \Ced\CsRma\Model\RequestFactory
     */
    
    public $rmaRequestFactory;

    /**
     * Group service
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    public $groupRepository;
    
    /**
     * @var \Ced\CsRma\Helper\Config $rmaConfigHelper
     */
    
    public $rmaConfigHelper;

    /**
     * Constructor
     *
     * @param \Ced\CsRma\Helper\Data $rmaDataHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Ced\CsRma\Model\RmanotificationFactory $rmaNotificationFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Ced\CsRma\Model\RequestFactory $rmaRequestFactory
     * @param \Ced\CsRma\Model\RmaitemsFactory $rmaItemFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */

    public function __construct(
        \Ced\CsRma\Helper\Data $rmaDataHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Sales\Api\OrderAddressRepositoryInterface $orderAddressRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Ced\CsRma\Helper\Config $rmaConfigHelper,
        \Ced\CsRma\Model\RequestFactory $rmaRequestFactory,
        \Ced\CsRma\Model\RmaitemsFactory $rmaItemFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        $this->orderAddressRepository = $orderAddressRepository;
        $this->groupRepository = $groupRepository;
        $this->rmaDataHelper = $rmaDataHelper;
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        $this->rmaItemFactory = $rmaItemFactory;
        $this->rmaConfigHelper = $rmaConfigHelper;
        $this->rmaRequestFactory = $rmaRequestFactory;
        parent::__construct($context,$data);
        $this->setRmaRequestId($this->getRequest()->getParam('id', false));
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'customer_rma_chat',
            'Ced\CsRma\Block\Customer\Chat'
        );

        $this->addChild(
            'customer_rma_history',
            'Ced\CsRma\Block\Customer\History'
        );
        
        $this->addChild(
            'customer_rma_notification',
            'Ced\CsRma\Block\Customer\Notification'
        );
        
        $this->pageConfig->getTitle()->set(__('RMA ID # %1', $this->getRmaData()->getRmaId()));
    }

    public function getOrderData()
    {
        return $this->rmaDataHelper->getOrderCollection($this->getRmaData()->getOrderId());
    }
    /**
     * Retrieve current request model instance
     *
     * @return \Ced\Rma\Model\Request
     */
    public function getRmaData()
    {
        return $this->_coreRegistry->registry('current_rma');
    }

    /**
     * Retrieve rma item model instance
     *
     * @return \Ced\Rma\Model\Rmaitems
     */
    public function getRmaItemsData()
    {
        $rmaItems = $this->rmaItemFactory->create()
            ->getCollection()
            ->addFieldToFilter('rma_request_id',$this->getRmaData()
                    ->getRmaRequestId());
        return $rmaItems;
    }

    

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->getUrl('*/*/index');
        }
        return $this->getUrl('*/*/form');
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return \Magento\Framework\Phrase
     */
    public function getBackTitle()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return __('Back to My Orders');
        }
        return __('View Another Order');
    }

    /*public function checkRmaForOrder()
    {
        if($this->getRMARequested())
        {
            $isOrder = $this->rmaRequestFactory->create()->load($this->getRMARequested()->getOrderId(), 'order_id')->getData();
            if (empty($isOrder))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        return false;
    }*/
   /* public function getPrintLabelUrl()
    {
        return $this->getUrl('rma/customerrma/printLabel', array('id' => $this->getRmaData()->getRmaRequestId()));
    }*/
}