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

class Notification extends \Magento\Framework\View\Element\Template
{
      /**
    * @var Ced\CsRma\Model\RmanotificationFactory
    */

    public $rmaNotificationFactory;
    /**
     * @var \Ced\CsRma\Helper\Config
     */
    public $rmaConfigHelper;
    
    /**
     * @var \Ced\CsRma\Model\RmachatFactory
     */
    public $rmaChatFactory;
	
    /**
     * @var string
     */
    protected $_template = 'customer/notification.phtml';

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\Rma\Model\RmachatFactory $rmaChatFactory
     * @param \Ced\Rma\Helper\Config $rmaConfigHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */

    public function __construct(
        \Ced\CsRma\Model\RmanotificationFactory $rmaNotificationFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsRma\Model\RmachatFactory $rmaChatFactory,
        \Ced\CsRma\Helper\Config $rmaConfigHelper,
       \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ){ 
        $this->rmaNotificationFactory = $rmaNotificationFactory;
        $this->customerSession = $customerSession;
        $this->rmaConfigHelper = $rmaConfigHelper;
        $this->rmaChatFactory = $rmaChatFactory;
        parent::__construct($context,$data);
    }
    /**
     * Retrieve rma notification model instance
     *
     * @return \Ced\Rma\Model\Rmanotification
     */
    public function getRmaNotificationData()
    {
        $id = $this->getRequest()->getParam('id');
        $rmaNotification =  $this->rmaNotificationFactory->create()
            ->getCollection()
            ->addFieldToFilter('rma_request_id',$id);
        return $rmaNotification;
    }
}
