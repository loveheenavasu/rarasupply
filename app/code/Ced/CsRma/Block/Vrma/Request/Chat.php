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
 * Class Chat
 * @package Ced\CsRma\Block\Vrma\Request
 */
class Chat extends \Ced\CsRma\Block\Vrma\Request\Edit
{
    /**
     * Chat constructor.
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

        $this->setTemplate('vrma/chat.phtml');
    }

    /**
     * @return $this|Edit
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'vendor_rma_history',
            'Ced\CsRma\Block\Vrma\Request\History'
        );
        $this->addChild(
            'vendor_rma_notification',
            'Ced\CsRma\Block\Vrma\Request\Notification'
        );
        return $this;
    }
}
