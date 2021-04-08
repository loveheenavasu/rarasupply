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

namespace Ced\Rewardsystem\Cron;

/**
 * Class Reward
 * @package Ced\Rewardsystem\Cron
 */
class Reward
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Ced\Rewardsystem\Model\RegisuserpointFactory
     */
    protected $regisuserpointFactory;

    /**
     * Reward constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->regisuserpointFactory = $regisuserpointFactory;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addReward()
    {

        $rewardenable = $this->scopeConfig->getValue('reward/setting/enable');
        if (!$rewardenable) {
            return;
        } else {

            $customers = $this->customerCollectionFactory->create()
                ->addAttributeToFilter('dob', ['in' => '1993-03-11']);
            $currday = $this->timezone->date()->format('m/d/y H:i:s');
            $birthPoint = $this->scopeConfig->getValue('reward/setting/birthday_point');
            if ($birthPoint) {
                foreach ($customers as $customer) {
                    $rewardModel = $this->regisuserpointFactory->create()->getCollection()
                        ->addFieldToFilter('customer_id', 12)
                        ->addFieldToFilter('is_birthday', 1)
                        ->setOrder('id', 'DESC')
                        ->getFirstItem()->getData();

                    if (count($rewardModel)) {

                        $datediff = strtotime($rewardModel['creating_date']) - strtotime($currday);

                        $daydiff = 1 + floor($datediff / (60 * 60 * 24));
                        if ($daydiff >= 365) {
                            $model = $this->regisuserpointFactory->create();
                            $model->setTitle('Received Rewardpoint for Birthday');
                            $model->setCreatingDate($currday);
                            $model->setStatus('complete');
                            $model->setIsBirthday(1);
                            $model->setPoint($birthPoint);
                            $model->setCustomerId($customer->getEntityId());
                            $model->save();
                        }

                    } else {
                        $model = $this->regisuserpointFactory->create();
                        $model->setTitle('Received Rewardpoint for Birthday');
                        $model->setCreatingDate($currday);
                        $model->setStatus('complete');
                        $model->setIsBirthday(1);
                        $model->setPoint($birthPoint);
                        $model->setCustomerId($customer->getEntityId());
                        $model->save();
                    }
                }
            }
        }
    }

}