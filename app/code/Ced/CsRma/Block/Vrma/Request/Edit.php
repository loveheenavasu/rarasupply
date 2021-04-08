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
 * Class Edit
 * @package Ced\CsRma\Block\Vrma\Request
 */
class Edit extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Ced\CsRma\Model\RmaitemsFactory
     */
    protected $rmaItemFactory;

    /**
     * @var \Ced\CsRma\Helper\Config $rmaConfigHelper
     */
    public $rmaDataHelper;

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    public $groupRepository;

    /**
     * Edit constructor.
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
        \Ced\CsRma\Model\RmaitemsFactory $rmaItemFactory,
        \Ced\CsRma\Helper\Data $rmaDataHelper,
        \Ced\CsRma\Model\RequestFactory $requestFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        \Ced\CsMarketplace\Model\Session $customerSession,
        UrlFactory $urlFactory
    )
    {
        $this->rmaItemFactory = $rmaItemFactory;
        $this->rmaDataHelper = $rmaDataHelper;
        $this->requestFactory = $requestFactory;
        $this->groupRepository = $groupRepository;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'csrma_vendor_chat',
            'Ced\CsRma\Block\Vrma\Request\Chat'
        );
        return parent::_prepareLayout();
    }


    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getRmaCollection()
    {
        $id = $this->getRequest()->getParam('rma_id');
        return $this->requestFactory->create()->load($id);
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->session->isLoggedIn()) {
            return $this->getUrl('*/*/index', array('_secure' => true, '_nosid' => true));
        }
        return $this->getUrl('*/*/form', array('_secure' => true, '_nosid' => true));
    }

    /**
     * @return array
     */
    public function getOrderData()
    {
        return $this->rmaDataHelper->getOrderCollection($this->getRmaCollection()->getOrderId());
    }

    /**
     * Return rma request order-item collection.
     *
     * @return array
     */
    public function getVendorItemCollection()
    {
        $vendor_item = $this->rmaItemFactory->create()->getCollection()
            ->addFieldToFilter('rma_request_id', $this->getRequest()->getParam('rma_id'));
        return $vendor_item;
    }

}
