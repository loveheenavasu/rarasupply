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

/**
 * Class Followinsta
 * @package Ced\Rewardsystem\Controller\Referral
 */
class Followinsta extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Ced\Rewardsystem\Model\RegisuserpointFactory
     */
    protected $regisuserpointFactory;

    /**
     * Followinsta constructor.
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory
     * @param Context $context
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory,
        Context $context
    )
    {
        $this->session = $session;
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;
        $this->jsonFactory = $jsonFactory;
        $this->regisuserpointFactory = $regisuserpointFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $customerId = $this->session->getCustomerId();
        $store = $this->scopeConfig;
        $instafollowPoint = $store->getValue('reward/setting/insta_follow_points');
        $today = $this->timezone->date()->format('y-m-d');

        $resultJson = $this->jsonFactory->create();
        $params = $this->getRequest()->getPostValue();

        $token = $params['token'];
        $userId = $params['userid'];
        $url = "https://api.instagram.com/v1/users/" . $userId . "/relationship?access_token=" . $token;

        $attachment = array(
            'access_token' => $token,
            'action' => 'follow'
        );

        try {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  //to suppress the curl output
            $result = curl_exec($ch);
            $result = json_decode(json_encode($result), True);
            $result = json_decode($result, true);

            curl_close($ch);
            if (isset($result['data']['outgoing_status']) && $result['data']['outgoing_status'] != 'follows') {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $attachment);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  //to suppress the curl output
                $output = json_decode(json_encode(curl_exec($ch)), true);
                $output = json_decode($output, true);
                curl_close($ch);

                if (isset($output['data']['outgoing_status'])) {
                    if ($output['data']['outgoing_status'] == 'follows') {
                        $model = $this->regisuserpointFactory->create()->getCollection()
                            ->addFieldToFilter('customer_id', $customerId)
                            ->addFieldToFilter('is_register', 1)->getFirstItem();

                        if (!$model->getFollowOnInsta()) {
                            $Rmodel = $this->regisuserpointFactory->create()->load($model->getId())->setFollowOnInsta(1)->save();
                            $rmodel = $this->regisuserpointFactory->create();
                            $rmodel->setTitle('Received Rewardpoints for following on Instagram');
                            $rmodel->setCreatingDate($today);
                            $rmodel->setStatus('complete');
                            $rmodel->setPoint($instafollowPoint);
                            $rmodel->setCustomerId($customerId);
                            $rmodel->save();
                        }
                        $resultJson->setData($output['data']['outgoing_status']);
                        return $resultJson;
                    } else {
                        $resultJson->setData($output['data']['outgoing_status']);
                        return $resultJson;

                    }
                } else {
                    $resultJson->setData(__('not able to follow now'));
                    return $resultJson;

                }
            } elseif (isset($result['data']['outgoing_status']) && $result['data']['outgoing_status'] == 'follows') {
                $model = $this->regisuserpointFactory->create()->getCollection()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('is_register', 1)->getFirstItem();

                if (!$model->getFollowOnInsta()) {
                    $Rmodel = $this->regisuserpointFactory->create()->load($model->getId())->setFollowOnInsta(1)->save();
                    $rmodel = $this->regisuserpointFactory->create();
                    $rmodel->setTitle('Received Rewardpoints for following on Instagram');
                    $rmodel->setCreatingDate($today);
                    $rmodel->setStatus('complete');
                    $rmodel->setPoint($instafollowPoint);
                    $rmodel->setCustomerId($customerId);
                    $rmodel->save();
                }
                $resultJson->setData(__('already following'));
                return $resultJson;

            } else {
                $resultJson->setData(__('not able to follow now'));
                return $resultJson;

            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
    }
}