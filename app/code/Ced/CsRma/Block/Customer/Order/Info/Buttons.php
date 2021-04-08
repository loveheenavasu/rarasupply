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

namespace Ced\CsRma\Block\Customer\Order\Info;

/**
 * Class Buttons
 * @package Ced\CsRma\Block\Customer\Order\Info
 */
class Buttons extends \Magento\Sales\Block\Order\Info\Buttons
{
    /**
     * @var string
     */
    protected $_template = 'order/info/buttons.phtml';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Buttons constructor.
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    )
    {
        $this->orderFactory = $orderFactory;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_isScopePrivate = true;
        parent::__construct($context, $registry, $httpContext, $data);
    }

    /**
     * @param $order
     * @return string
     */
    public function getRmaUrl($order)
    {
        return $this->getUrl('csrma/customerrma/new/', ['order_id' => $order->getIncrementId()]);
    }

    /**
     *
     * @param int $orderId
     * returns whether orders can be cancel or not
     */
    public function cancelOrder($orderId)
    {
        $order = $this->orderFactory->create()->load($orderId);
        $store = $this->_scopeConfig;
        if (!$store->getValue('ced_csmarketplace/rma_general_group/cancel_order')) {
            return false;
        } else {
            if ($order && $order->canCancel()) {
                return true;
            } else {
                return false;
            }
        }
    }
}
