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
namespace Ced\CsRma\Block\Adminhtml\AllRma;

class Notification extends \Magento\Backend\Block\Template
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
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry = null;

    /**
     * @var string
     */
    protected $_template = 'edit/notification.phtml';

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Ced\CsRma\Helper\Config $rmaConfigHelper,
        \Ced\CsRma\Model\RmanotificationFactory $rmaNotificationFactory,
        \Ced\CsRma\Model\RmachatFactory $rmaChatFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->rmaNotificationFactory = $rmaNotificationFactory;
        $this->rmaConfigHelper = $rmaConfigHelper;
        parent::__construct($context, $data);
        $this->rmaChatFactory = $rmaChatFactory;
        $this->_coreRegistry = $registry;
        $this->adminHelper = $adminHelper;
    }
    
    /**
     * Replace links in string
     *
     * @param array|string $data
     * @param null|array $allowedTags
     * @return string
     */

   public function getFullNotification()
    {
        $notification = $this->rmaNotificationFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('rma_request_id',
                    $this->getRequest()->getParam('id'));
        return $notification;
    }
}
