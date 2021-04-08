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

namespace Ced\CsRma\Block\Vrma\Request;

use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class History
 * @package Ced\CsRma\Block\Vrma\Request
 */
class History extends \Ced\CsRma\Block\Vrma\Request\Chat
{
    /**
     * @var \Ced\CsRma\Model\ResourceModel\Rmachat\CollectionFactory
     */
    protected $rmachatFactory;

    /**
     * @var \Ced\CsRma\Helper\Config
     */
    public $rmaConfigHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * History constructor.
     * @param \Ced\CsRma\Model\ResourceModel\Rmachat\CollectionFactory $rmachatFactory
     * @param \Ced\CsRma\Model\RmaitemsFactory $rmaItemFactory
     * @param \Ced\CsRma\Helper\Data $rmaDataHelper
     * @param \Ced\CsRma\Model\RequestFactory $requestFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param \Ced\CsMarketplace\Model\Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Ced\CsRma\Model\ResourceModel\Rmachat\CollectionFactory  $rmachatFactory,
        \Ced\CsRma\Model\RmaitemsFactory $rmaItemFactory,
        \Ced\CsRma\Helper\Data $rmaDataHelper,
        \Ced\CsRma\Helper\Config $rmaConfigHelper,
        \Ced\CsRma\Model\RequestFactory $requestFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        \Ced\CsMarketplace\Model\Session $customerSession,
        UrlFactory $urlFactory
    )
    {
        $this->rmachatFactory = $rmachatFactory;
        $this->rmaConfigHelper = $rmaConfigHelper;
        $this->storeManager = $context->getStoreManager();
        parent::__construct(
            $rmaItemFactory,
            $rmaDataHelper,
            $requestFactory,
            $groupRepository,
            $vendorFactory,
            $customerFactory,
            $context,
            $customerSession,
            $urlFactory
        );
        $this->setTemplate('vrma/history.phtml');
    }

    /**
     * @return $this|Chat|Edit
     */
    protected function _prepareLayout()
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChatCollection()
    {
        $id = $this->getRmaCollection()->getId();
        $model = $this->rmachatFactory->create()
            ->addFieldToFilter('rma_request_id', $id);
        return $model;
    }

    /**
     * Retrieve customer model using customer id from rma request id
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getVendorRmaCustomer()
    {
        $customerId = $this->getRmaCollection()->getCustomerId();
        $customer = $this->_customerFactory->create()
            ->load($customerId)->getName();
        return $customer;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChatFile()
    {
        $store = $this->storeManager;
        return $store->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
