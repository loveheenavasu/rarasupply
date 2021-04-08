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

namespace Ced\CsRma\Block\Adminhtml\AllRma;

/**
 * Class History
 * @package Ced\CsRma\Block\Adminhtml\AllRma
 */
class History extends \Magento\Backend\Block\Template
{

    /**
     * @var \Ced\CsRma\Model\RmachatFactory
     */
    public $rmaChatFactory;

    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry = null;

    /**
     * @var string
     */
    protected $_template = 'edit/history.phtml';

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    public $_storeManager;

    /**
     * History constructor.
     * @param \Ced\CsRma\Model\RmachatFactory $rmaChatFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Ced\CsRma\Model\RmachatFactory $rmaChatFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->rmaChatFactory = $rmaChatFactory;
        $this->_coreRegistry = $registry;
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getChatDataCollection()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->rmaChatFactory->create()
            ->getCollection()
            ->addFieldToFilter('rma_request_id', $id);
        return $model;

    }

    /**
     * Retrieve customer model using customer id from rma request id
     * @return \Magento\Sales\Model\Order
     */
    public function getRmaCustomer()
    {
        $customerId = $this->_coreRegistry->registry('ced_csrma_request')->getCustomerId();
        $customer = $this->customerFactory->create()
            ->load($customerId)->getName();
        return $customer;
    }
}
