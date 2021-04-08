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

namespace Ced\Rewardsystem\Block\Referral;

use Magento\Framework\View\Element\Template\Context;

/**
 * Class Invite
 * @package Ced\Rewardsystem\Block\Referral
 */
class Invite extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_getSession;

    /**
     * @var \Ced\Rewardsystem\Model\RegisuserpointFactory
     */
    protected $regisuserpointFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * Invite constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_getSession = $customerSession;
        $this->regisuserpointFactory = $regisuserpointFactory;
        $this->timezone = $timezone;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set("Refer to Friends");
        return $this;
    }


    /**
     * @return array
     */
    public function getReferUrl()
    {
        $customerId = $this->_getSession->getCustomerId();
        $RewardModel = $this->regisuserpointFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('is_register', 1)->getFirstItem();

        if (!$RewardModel || !$RewardModel->getId()) {
            $RewardModel = $this->getReferCoupon($customerId);
        }
        $referCode = $RewardModel->getReferCode();
        $url = $this->getUrl('customer/account/create/', ['refer_code' => $referCode]);

        return ['url' => $url, 'code' => $referCode];
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function getReferCoupon($customerId)
    {

        $today = $this->timezone->date()->format('m/d/y H:i:s');
        $model = $this->regisuserpointFactory->create();
        $model->setCustomerId($customerId);
        $model->setPoint(0);
        $model->setTitle('Refer Code Generated');
        $model->setCreatingDate($today);
        $model->setStatus('complete');
        $model->setPointUsed(0);
        $model->setIsRegister(1);
        $model->setReferCode($this->randomString(5));
        $model->save();

        $RewardModel = $this->regisuserpointFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('is_register', 1)->getFirstItem();
        return $RewardModel;
    }

    /**
     * @return mixed
     */
    public function isfollowonInsta()
    {
        $customerId = $this->_getSession->getCustomerId();
        $model = $this->regisuserpointFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('is_register', 1)->getFirstItem();

        return $model->getFollowOnInsta();
    }

    /**
     * @param $length
     * @return string
     */
    function randomString($length)
    {
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

}