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

/**
 * Class SendInviteEmail
 * @package Ced\Rewardsystem\Controller\Referral
 */
class SendInviteEmail extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Ced\Rewardsystem\Helper\Data
     */
    protected $rewardsystemHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * SendInviteEmail constructor.
     * @param \Ced\Rewardsystem\Helper\Data $rewardsystemHelper
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct
    (
        \Ced\Rewardsystem\Helper\Data $rewardsystemHelper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->_getSession = $customerSession;
        $this->rewardsystemHelper = $rewardsystemHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {

            if (!$this->_getSession->isLoggedIn()) {
                $this->messageManager->addErrorMessage(__('Please login first'));
                $this->_redirect('customer/account/login');
                return;
            }

            $customer = $this->_getSession->getCustomer();

            $customer_Id = $customer->getId();
            $helper = $this->rewardsystemHelper;

            $emails = $this->getRequest()->getPost('emails');
            $emails = empty($emails) ? $emails : explode(',', $emails);

            $error = false;
            $subject = (string)$this->getRequest()->getPost('subject');
            $message = (string)$this->getRequest()->getPost('message');
            $referral_url = $this->getRequest()->getPost('referral_url');

            if ($message) {
                $message = nl2br(htmlspecialchars($message));
                if (empty($emails)) {
                    $error = __('Please enter an email address.');
                } else {
                    if ($emails) {
                        foreach ($emails as $index => $email) {
                            $email = trim($email);
                            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                                $error = __('Please enter a valid email address.');
                                break;
                            }
                            $Emails[$index] = $email;
                        }
                    }
                }
            }

            if ($error) {
                $this->messageManager->addErrorMessage($error);
                $this->_redirect('*/*/index');
                return;
            }
            $Emails = array_unique($Emails);

            $sendemail = $helper->sendInvitationEmail($emails, $message, $subject, $referral_url, $customer_Id);

            if ($sendemail == false) {
                $this->messageManager->addErrorMessage(__('Unable To Send Email'));
            } else {
                $this->messageManager->addSuccessMessage(__('Your invitation is successfully sent to %1 recipients', $sendemail));
            }
            $this->_redirect('*/*/index');
            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
    }
}