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
 * Class Notification
 * @package Ced\CsRma\Block\Vrma\Request
 */
class Notification extends \Ced\CsRma\Block\Vrma\Request\Chat
{
    /**
     * @var \Ced\CsRma\Model\ResourceModel\Rmanotification\CollectionFactory
     */
    protected $rmanotificationcollectionFactory;

    /**
     * Notification constructor.
     * @param \Ced\CsRma\Model\ResourceModel\Rmanotification\CollectionFactory $rmanotificationcollectionFactory
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
        \Ced\CsRma\Model\ResourceModel\Rmanotification\CollectionFactory $rmanotificationcollectionFactory,
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
        $this->rmanotificationcollectionFactory = $rmanotificationcollectionFactory;
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
        $this->setTemplate('vrma/notification.phtml');
    }

    /**
     * @return $this|Chat|Edit
     */
    protected function _prepareLayout()
    {
        return $this;
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
        $id = $this->getRmaCollection()->getId();
        $notification = $this->rmanotificationcollectionFactory->create()
            ->addFieldToFilter('rma_request_id', $id);
        return $notification;
    }

}
