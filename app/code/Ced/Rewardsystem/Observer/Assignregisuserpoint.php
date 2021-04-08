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
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Rewardsystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class Assignregisuserpoint
 * @package Ced\Rewardsystem\Observer
 */
class Assignregisuserpoint implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezoneInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Ced\Rewardsystem\Model\RegisuserpointFactory
     */
    protected $regisuserpointFactory;

    /**
     * Assignregisuserpoint constructor.
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     * @param \Magento\Framework\Registry $registry
     * @param RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\Registry $registry,
        RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory
    )
    {
        $this->_timezoneInterface = $timezoneInterface;
        $this->_coreRegistry = $registry;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->regisuserpointFactory = $regisuserpointFactory;
    }

    /**
     * custom event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_coreRegistry->registry('customerid')) {
            return $this;
        } else {
            $parentId = '';
            $referReward = '';
            $parentCustomerPoints = '';
            $store = $this->scopeConfig;
            if ($this->request->getParam('refer_code')) {
                $referCode = $this->request->getParam('refer_code');
                $RewardModel = $this->regisuserpointFactory->create()->load($referCode, 'refer_code')->getData();
                if (count($RewardModel)) {
                    $parentId = $RewardModel['customer_id'];
                }

                $referReward = $store->getValue('reward/setting/referal_point');
                $parentCustomerPoints = $store->getValue('reward/setting/signup_point');
            }
            $today = $this->_timezoneInterface->date()->format('m/d/y H:i:s');

            $productshow = $this->scopeConfig;
            $registrationPoint = $productshow->getValue('reward/setting/registration_point', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $event = $observer->getEvent();
            $customer = $event->getCustomer();
            $rewardCollection = $this->regisuserpointFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $customer->getId())
                ->addFieldToFilter('is_register', 1)->getFirstItem()->getData();

            if (!count($rewardCollection)) {

                $model = $this->regisuserpointFactory->create();
                $customerid = $customer->getId();
                $model->setCustomerId($customerid);

                $userAgent = $this->request->getServer('HTTP_USER_AGENT');

                $registrationPoint = ($registrationPoint > 0) ? $registrationPoint : 0;
                $model->setPoint($registrationPoint);
                $model->setReceivedPoint($registrationPoint);
                $model->setTitle('Received Rewardpoint for registering successfully');
                $model->setCreatingDate($today);
                $model->setUpdatedAt($today);
                $model->setStatus('complete');
                $model->setReferCode($this->randomString(5));
                $model->setParentCustomer($parentId);
                $model->setIsRegister(1);
                $this->_coreRegistry->register('customerid', $customerid);
                $model->save();

                if ($referReward) {
                    $rmodel = $this->regisuserpointFactory->create();
                    $rmodel->setTitle('Received Rewardpoint for referral signup');
                    $rmodel->setCreatingDate($today);
                    $rmodel->setUpdatedAt($today);
                    $rmodel->setStatus('complete');
                    $rmodel->setPoint($referReward);
                    $rmodel->setReceivedPoint($referReward);
                    $rmodel->setCustomerId($customerid);
                    $rmodel->save();
                }

                if ($parentCustomerPoints) {
                    $rcmodel = $this->regisuserpointFactory->create();
                    $rcmodel->setTitle('Received Rewardpoint due to referral signup');
                    $rcmodel->setCreatingDate($today);
                    $rcmodel->setUpdatedAt($today);
                    $rcmodel->setStatus('complete');
                    $rcmodel->setPoint($parentCustomerPoints);
                    $rcmodel->setReceivedPoint($parentCustomerPoints);
                    $rcmodel->setCustomerId($parentId);
                    $rcmodel->save();
                }
            }
        }
        return;
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