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

namespace Ced\CsRma\Controller\Adminhtml\AllRma;

use Magento\Backend\App\Action\Context;
use Ced\CsRma\Model\RmachatFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ced\CsRma\Helper\Data;
use Ced\CsRma\Helper\Email;
use Ced\CsRma\Model\RequestFactory;

/**
 * Class Chat
 * @package Ced\CsRma\Controller\Adminhtml\AllRma
 */
class Chat extends \Magento\Backend\App\Action
{
    /**
     * @var Ced\CsRma\Model\RequestFactory
     */
    protected $rmaRequestFactory;

    /**
     * @var Ced\CsRma\Helper\Data
     */
    public $rmaDataHelper;

    /**
     * @var Ced\CsRma\Model\RmachatFactory
     */
    protected $rmaChatFactory;

    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var Email
     */
    protected $rmaEmailHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * Chat constructor.
     * @param Data $rmaDataHelper
     * @param RequestFactory $rmaRequestFactory
     * @param Email $rmaEmailHelper
     * @param DateTime $dateTime
     * @param RmachatFactory $rmaChatFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param Context $context
     */
    public function __construct(
        Data $rmaDataHelper,
        RequestFactory $rmaRequestFactory,
        Email $rmaEmailHelper,
        DateTime $dateTime,
        RmachatFactory $rmaChatFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        Context $context
    )
    {
        $this->rmaRequestFactory = $rmaRequestFactory;
        $this->rmaEmailHelper = $rmaEmailHelper;
        $this->rmaDataHelper = $rmaDataHelper;
        $this->dateTime = $dateTime;
        $this->rmaChatFactory = $rmaChatFactory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context);
    }

    /**
     * Add order comment action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $postData = $this->getRequest()->getParams();
        if ($id = $this->getRequest()->getParam('id')) {
            if (empty($postData['comment'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a comment.'));
            }

            $rmaChat = $this->rmaChatFactory->create();
            $comment = trim(strip_tags($postData['comment']));
            $data = ['rma_request_id' => $id,
                'created_at' => $this->dateTime->gmtDate(),
                'chat_flow' => \Ced\CsRma\Model\Request::OWNER_ADMIN,  //when send from admin
                'chat' => $comment
            ];
            $rmaChat->setData($data);
            $files = $this->getRequest()->getFiles()->toArray();
            $file = '';
            if (!empty($files['rma_file']['name'])) {
                $file = $this->rmaDataHelper->getRmaImgUpload($postData);
            }
            if (!is_array($file)) {
                $rmaChat->setData('file', $file);
            } else {

                $this->messageManager->addErrorMessage(__($file[0]));
                return $this->resultRedirectFactory->create()->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }

            try {

                $rmaChat->save();
                $this->messageManager->addSuccessMessage(__('Message Sent Successfully'));
                $email = $this->prepareTemplateContent($comment, $id);
                return $this->resultRedirectFactory->create()->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];

            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We cannot send message.')];
            }
        }
        return $this->resultRedirectFactory->create()->setPath('csrma/*/');
    }

    /**
     * prepare template content for email
     * @param execute
     */
    protected function prepareTemplateContent($comment, $id)
    {
        $requestModel = $this->rmaRequestFactory->create()->load($id);
        $store = $requestModel->getStoreId();
        $customer = $requestModel->getCustomerName();
        $email = $requestModel->getCustomerEmail();
        $url = $this->storeManager->getStore()->getBaseUrl();
        $customer_info[] = ['name' => $customer, 'email' => $email, 'url' => $url . 'csrma/customerrma/view/id/' . $id];
        $scopeConfig = $this->scopeConfig;
        $admin_name = $scopeConfig->getValue('ced_csmarketplace/rma_general_group/dept_name') ? $scopeConfig->getValue('ced_csmarketplace/rma_general_group/dept_name') : "Admin";
        $admin_email = $scopeConfig->getValue('ced_csmarketplace/rma_general_group/dept_email') ? $scopeConfig->getValue('ced_csmarketplace/rma_general_group/dept_email') : "support@email.com";
        $sender = ['email' => $admin_email, 'name' => $admin_name];
        if($requestModel->getVendorId() != 'admin'){
           $vendor_info = $this->vendorFactory->create()->load($requestModel->getVendorId());
           array_push($customer_info, ['name' => $vendor_info->getName(), 'email' => $vendor_info->getEmail(), 'url' => $url . 'csrma/vrma/edit/rma_id/' . $id]);
        }
        $this->rmaEmailHelper->sendStatusEmail($comment, $sender, $customer_info, $store);
    }
}
