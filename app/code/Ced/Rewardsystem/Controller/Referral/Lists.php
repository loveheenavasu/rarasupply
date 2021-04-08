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

namespace Ced\Rewardsystem\Controller\Referral;

use Magento\Framework\App\Action\Context;
use Ced\Rewardsystem\Model;

/**
 * Class Lists
 * @package Ced\Rewardsystem\Controller\Referral
 */
class Lists extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var Model\ResourceModel\Regisuserpoint\CollectionFactory
     */
    protected $regisuserpointCollectionFactory;

    /**
     * @var Model\RegisuserpointFactory
     */
    protected $regisuserpointFactory;

    /**
     * Lists constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param Model\ResourceModel\Regisuserpoint\CollectionFactory $regisuserpointCollectionFactory
     * @param Model\RegisuserpointFactory $regisuserpointFactory
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        Model\ResourceModel\Regisuserpoint\CollectionFactory $regisuserpointCollectionFactory,
        Model\RegisuserpointFactory $regisuserpointFactory,
        Context $context
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->timezone = $timezone;
        $this->regisuserpointCollectionFactory = $regisuserpointCollectionFactory;
        $this->regisuserpointFactory = $regisuserpointFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
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
                    $rewardModel = $this->regisuserpointCollectionFactory->create()
                        ->addFieldToFilter('customer_id', 12)
                        ->addFieldToFilter('is_birthday', 1)
                        ->setOrder('id', 'DESC')->getFirstItem()->getData();

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