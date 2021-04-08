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

namespace Ced\CsRma\Controller\Vrma;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Chat
 * @package Ced\CsRma\Controller\Vrma
 */
class Chat extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Ced\CsRma\Model\RmachatFactory
     */
    protected $rmachatFactory;

    /**
     * @var \Ced\CsRma\Helper\Data
     */
    protected $csrmaHelper;

    /**
     * @var \Ced\CsRma\Model\RequestFactory
     */
    protected $requestFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Ced\CsRma\Helper\Email
     */
    protected $emailHelper;

    /**
     * Chat constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Ced\CsRma\Model\RmachatFactory $rmachatFactory
     * @param \Ced\CsRma\Helper\Data $csrmaHelper
     * @param \Ced\CsRma\Model\RequestFactory $requestFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsRma\Helper\Email $emailHelper
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Ced\CsRma\Model\RmachatFactory $rmachatFactory,
        \Ced\CsRma\Helper\Data $csrmaHelper,
        \Ced\CsRma\Model\RequestFactory $requestFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsRma\Helper\Email $emailHelper,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
        $this->dateTime = $dateTime;
        $this->rmachatFactory = $rmachatFactory;
        $this->csrmaHelper = $csrmaHelper;
        $this->requestFactory = $requestFactory;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->scopeConfig = $scopeConfig;
        $this->vendorFactory = $vendorFactory;
        $this->emailHelper = $emailHelper;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * Get the value of customer session
     * @return Session
     */
    public function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $postData = $this->getRequest()->getParams();

        $resultRedirect = $this->resultRedirectFactory->create();

        $dateTime = $this->dateTime;
        if ($id = $this->getRequest()->getParam('id')) {
            if (empty($postData['chat'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please enter message.'));
            }

            $rmaChat = $this->rmachatFactory->create();
            $file = '';
            $fileIndex = 'rma_file';
            if (!empty($_FILES[$fileIndex]['name'])) {
                $file = $this->csrmaHelper->getRmaImgUpload($postData);
            }

            $comment = trim(strip_tags($postData['chat']));
            $data = ['rma_request_id' => $id,
                'created_at' => $dateTime->gmtDate(),
                'chat_flow' => \Ced\CsRma\Model\Request::OWNER_VENDOR,  //when send form vendor
                'chat' => $comment,
                'vendor_id' => $postData['vendor_id']
            ];
            $rmaChat->setData($data);
            if (!is_array($file)) {
                $rmaChat->setData('file', $file);
            } else {

                $this->messageManager->addErrorMessage(__($file[0]));
                return $resultRedirect->setPath('*/*/edit', ['rma_id' => $this->getRequest()->getParam('id')]);
            }

            try {
                $rmaChat->save();
                $this->messageManager->addSuccessMessage(__('Message Sent Successfully'));
                $email = $this->prepareTemplateContent($comment, $id);
                return $resultRedirect->setPath('*/*/edit', ['rma_id' => $this->getRequest()->getParam('id')]);

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];

            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We cannot send message.')];
            }
        }
        return $resultRedirect->setPath('*/*/edit', ['rma_id' => $this->getRequest()->getParam('id')]);
    }

    /**
     * prepare template content for email
     * @param execute
     */
    protected function prepareTemplateContent($comment, $id)
    {
        $customer = [];
        $requestModel = $this->requestFactory->create()->load($id);
        $customer_info = $requestModel->getCustomerName();
        $email = $requestModel->getCustomerEmail();
        $url = $this->storeManager->getStore()->getBaseUrl();
        $adminUrl = $this->url->getUrl('csrma/allrma/chat', ['id' => $id]);
        $customer[] = ['name' => $customer_info, 'email' => $email, 'url' => $url . 'csrma/customerrma/view/id/' . $id];
        $scopeConfig = $this->scopeConfig;
        if (!empty($scopeConfig->getValue('ced_csmarketplace/rma_general_group/dept_email')) && !empty($scopeConfig->getValue('ced_csmarketplace/rma_general_group/dept_name')))
            array_push($customer, ['name' => $scopeConfig->getValue('ced_csmarketplace/rma_general_group/dept_name'),
                'email' => $scopeConfig->getValue('ced_csmarketplace/rma_general_group/dept_email'), 'url' => $adminUrl]);
        $store = $requestModel->getStoreId();

        $vendor_info = $this->vendorFactory->create()->load($requestModel->getVendorId());
        $sender = ['email' => $vendor_info->getEmail(), 'name' => $vendor_info->getName()];
        $this->emailHelper->sendStatusEmail($comment, $sender, $customer, $store);
    }
}
