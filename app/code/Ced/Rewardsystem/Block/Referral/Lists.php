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

namespace Ced\ReferralSystem\Block\Referral;

use Magento\Framework\View\Element\Template\Context;

/**
 * Class Lists
 * @package Ced\ReferralSystem\Block\Referral
 */
class Lists extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_getSession;

    /**
     * Lists constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->_getSession = $customerSession;
        parent::__construct($context);
    }

    public function _construct()
    {
        $customer = $this->_getSession->getCustomer();
        $customer_Id = $customer->getId();
        $productModel = $this->getCollection()
            ->addFieldtoFilter('customer_id', [
                'customer_id' => $customer_Id
            ]);
        $this->setCollection($productModel);
    }

    /**
     * Prepare Pager Layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'my.custom.pager')
                ->setLimit(5)->setCollection($this->getCollection());
            $this->setChild('pager', $pager);
        }
        $this->pageConfig->getTitle()->set("Referral Report");
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return int
     */
    public function pendingamount()
    {
        $amount = 0;
        $referred_list = $this->getCollection();
        foreach ($referred_list as $value) {
            $amount += $value['amount'];
        }
        return $amount;
    }

    /**
     * @return int|void
     */
    public function pendingreferral()
    {
        $customer = $this->_getSession->getCustomer();
        $customer_Id = $customer->getId();
        $pendingreferral = $this->getCollection()
            ->addFieldtoFilter('customer_id', [
                'customer_id' => $customer_Id
            ])->addFieldtoFilter('signup_status', 0)->getData();
        return sizeof($pendingreferral);
    }

    /**
     * @return int|void
     */
    public function registeredreferral()
    {
        $customer = $this->_getSession->getCustomer();
        $customer_Id = $customer->getId();
        $registeredreferral = $this->getCollection()
            ->addFieldtoFilter('customer_id', [
                'customer_id' => $customer_Id
            ])->addFieldtoFilter('signup_status', 1)->getData();
        return sizeof($registeredreferral);
    }
}