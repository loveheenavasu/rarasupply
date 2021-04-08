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
 * Class Chat
 * @package Ced\CsRma\Block\Adminhtml\AllRma
 */
class Chat extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;

    /**
     * @var \Ced\CsRma\Model\RequestFactory
     */
    protected $requestFactory;

    /**
     * Chat constructor.
     * @param \Ced\CsRma\Model\RequestFactory $requestFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Ced\CsRma\Model\RequestFactory $requestFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    )
    {

        $this->adminHelper = $adminHelper;
        $this->requestFactory = $requestFactory;
        $this->setTemplate('edit/chat.phtml');
        parent::__construct($context, $data);
    }

    /**
     * Preparing global layout
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'admin_rma_history',
            'Ced\CsRma\Block\Adminhtml\AllRma\History'
        );

        $this->addChild(
            'admin_rma_notification',
            'Ced\CsRma\Block\Adminhtml\AllRma\Notification'
        );
        return parent::_prepareLayout();
    }

    /**
     * Get submit url
     * @return string|true
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('csrma/allrma/chat', ['id' => $this->getRequest()->getParam('id')]);
    }


    /**
     * Replace links in string
     * @param array|string $data
     * @param null|array $allowedTags
     * @return string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->adminHelper->escapeHtmlWithLinks($data, $allowedTags);
    }

    /**
     * Retrieve current order model instance
     * @return Mage_Sales_Model_Order
     */
    public function getRmaCollection()
    {
        $id = $this->getRequest()->getParam('id');
        return $this->requestFactory->create()->load($id);
    }
}
