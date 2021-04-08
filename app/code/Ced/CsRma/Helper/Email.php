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
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRma\Helper;

use Magento\Framework\Mail\MessageInterface;

/**
 * Class Email
 * @package Ced\CsRma\Helper
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{

    const T_CUSTOMER_BASE_EMAIL_TEMPLATE_XML_PATH = 'cedrma_section/rma_mail_group/customer_mail_template';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Ced\Rma\Helper\Config
     */
    protected $rmaConfigHelper;

    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $state;

    /**
     * Email constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Config $rmaConfigHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param MessageInterface $message
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $state
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Ced\CsRma\Helper\Config $rmaConfigHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        MessageInterface $message,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $state
    )
    {
        $this->rmaConfigHelper = $rmaConfigHelper;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->message = $message;
        $this->transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->state = $state;
        parent::__construct($context);

    }

    /**
     * Send status email  to customer
     *
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @return Mage_Customer_Model_Customer
     * @throws Mage_Core_Exception
     */
    public function sendStatusEmail($message, $sender, $customer, $storeId)
    {
        foreach ($customer as $value) {
            $this->_sendEmailTemplate(
                $message, $sender, $value, $storeId);
        }
        return $this;
    }

    /**
     * Send corresponding email template
     *
     * @param string $emailTemplate configuration path of email template
     * @param string $emailSender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @return Mage_Customer_Model_Customer
     */
    public function _sendEmailTemplate($template, $sender, $receiver, $storeId = null)
    {
        /*reference file vendor\magento\module-sales\Model\Order\Email\SenderBuilder.php */
        try {
            $storeId = $this->_storeManager->getStore()->getId();
            $templateId = 'admin_email_status_template';
            $templateParams = ['message' => $template, 'redirect_url' => $receiver['url']];

            $transportBuilder = $this->transportBuilder;
            $transportBuilder->addTo($receiver['email']);
            $transportBuilder->setTemplateIdentifier($templateId);
            $transportBuilder->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                    'store' => $storeId/*\Magento\Store\Model\Store::DEFAULT_STORE_ID*/,
                ]
            );
            $transportBuilder->setTemplateVars($templateParams);
            $transportBuilder->setFrom($sender);
            $transport = $transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
        }
        return $this;
    }

    /**
     * Send notification to customer
     *
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @return Mage_Customer_Model_Customer
     * @throws Mage_Core_Exception
     */
    public function sendNotificationEmail($message, $customer, $email, $storeId)
    {
        $sender = $this->rmaConfigHelper->getDepartmentChatName();
        $this->_sendEmailTemplate(
            $message, $sender,
            array('vendor' => $customer, 'email' => $email), $storeId
        );
        return $this;
    }

    /**
     * @param $data
     * @param $customerEmail
     * @param $customerName
     * @param $rmaid
     * @param $url
     * @param $template
     */
    public function sendNewRmaMail($data, $customerEmail, $customerName, $rmaid, $url, $template)
    {

        $this->state->suspend();
        try {
            $error = false;
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $sender = [
                'email' => $this->scopeConfig->getValue('ced_csmarketplace/rma_general_group/dept_email'),
                'name' => $this->scopeConfig->getValue('trans_email/ident_support/name'),
            ];

            $storeId = $this->_storeManager->getStore()->getId();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($template)// this code we have mentioned in the email_templates.xml
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                        'store' => $storeId/*\Magento\Store\Model\Store::DEFAULT_STORE_ID*/,
                    ]
                )
                ->setTemplateVars(['rmaid' => $rmaid, 'name' => $customerName, 'url' => $url])
                ->setFrom($sender)
                ->addTo($customerEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->state->resume();
            return;
        } catch (\Exception $e) {
            return;
        }
    }


    /**
     * @param $mail
     * @param $description
     * @param $Vname
     * @param $Vemail
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendTransactional($mail, $description, $Vname, $Vemail)
    {
        $senderEmail = $this->_scopeConfig->getValue('trans_email/ident_general/email');
        $senderName = "Admin";
        $storeId = $this->_storeManager->getStore()->getId();
        $this->state->suspend();
        try {
            $error = false;
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $Vsender = [
                'name' => $senderName,
                'email' => $senderEmail,
            ];


            $transport = $this->transportBuilder
                ->setTemplateIdentifier('send_disapatch_email_template')// this code we have mentioned in the email_templates.xml
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                        'store' => $storeId/*\Magento\Store\Model\Store::DEFAULT_STORE_ID*/,
                    ]
                )
                ->setTemplateVars(['answer' => $description, 'name' => $Vname])
                ->setFrom($Vsender)
                ->addTo($Vemail)
                ->getTransport();

            $transport->sendMessage();
            $this->state->resume();
            return;
        } catch (\Exception $e) {

            $this->messageManager->addError(
                __('We can\'t process your request right now. Sorry, that\'s all we know.' . $e->getMessage())
            );
            $this->_redirect('*/*/');
            return;
        }
    }

}
