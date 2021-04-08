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
 * @package     Ced_Rewardsystem
 * @author     CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Rewardsystem\Block\Checkout\MiniCart;

/**
 * Class Content
 * @package Ced\Rewardsystem\Block\Checkout\MiniCart
 */
class Content extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Content constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry, array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
    }

    /**
     * @return mixed
     */
    public function PointData()
    {
        $results['showPoint'] = 0;
        $results['customerLogin'] = $this->_customerSession->isLoggedIn();
        $results['urlRedirectLogin'] = $this->getBaseUrl() . 'customer/account/login/';
        return $results;
    }

}
