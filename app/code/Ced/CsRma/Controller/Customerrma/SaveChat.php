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

namespace Ced\CsRma\Controller\Customerrma;

/**
 * Class SaveChat
 * @package Ced\CsRma\Controller\Customerrma
 */
class SaveChat extends \Ced\CsRma\Controller\Link
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var redirect
     */
    protected $_redirect;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Ced\Rma\Model\RmachatFactory
     */
    protected $rmaChatFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Ced\Rma\Helper\Data
     */
    protected $rmaDataHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Ced\CsRma\Model\RequestFactory
     */
    protected $requestFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * SaveChat constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Ced\CsRma\Model\RmachatFactory $rmaChatFactory
     * @param \Ced\CsRma\Helper\Data $rmaDataHelper
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\CsRma\Model\RequestFactory $requestFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ced\CsRma\Helper\Config $configHelper
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Ced\CsRma\Model\RmachatFactory $rmaChatFactory,
        \Ced\CsRma\Helper\Data $rmaDataHelper,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsRma\Model\RequestFactory $requestFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\CsRma\Helper\Config $configHelper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
    )
    {
        $this->rmaDataHelper = $rmaDataHelper;
        $this->rmaChatFactory = $rmaChatFactory;
        $this->dateTime = $dateTime;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_redirect = $context->getRedirect();
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->requestFactory = $requestFactory;
        $this->vendorFactory = $vendorFactory;
        $this->storeManager = $storeManager;

        parent::__construct($configHelper, $context, $customerSession, $resultRedirectFactory);
    }

    /**
     * @param execute
     * return redirect page
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $chatModel = $this->rmaChatFactory->create();

        if ($this->getRequest()->getPost()) {

            $postData = $this->getRequest()->getParams();

            $data = ['rma_request_id' => $postData['id'],
                'chat' => $postData['chat'],
                'chat_flow' => \Ced\CsRma\Model\Request::OWNER_CUSTOMER, //for customer
                'created_at' => $this->dateTime->gmtDate()
            ];
            $file = '';
            $fileIndex = 'rma_file';
            if (!empty($_FILES[$fileIndex]['name'])) {
                $file = $this->rmaDataHelper->getRmaImgUpload($postData);

            }
            $chatModel->setData($data);
            if (!is_array($file)) {
                $chatModel->setData('file', $file);
            } else {

                $this->messageManager->addErrorMessage(__($file[0]));
                $url = $this->_buildUrl('*/*/view', ['id' => $chatModel->getRmaRequestId()]);

                return $this->resultRedirectFactory->create()->setUrl($this->_redirect->success($url));
            }

            try {
                $chatModel->save();
                $this->messageManager->addSuccessMessage(__('Your message has been sent sucessfully.'));

                $url = $this->_buildUrl('*/*/view', ['id' => $chatModel->getRmaRequestId()]);
                $customer = $this->customerSession->getCustomer()->getName();
                $email = $this->customerSession->getCustomer()->getEmail();
                $sender = ['name' => $customer, 'email' => $email];
                $storeId = $this->getStoreId();
                $message = $postData['chat'];
                $scopeConfig = $this->scopeConfig;
                $requestModel = $this->requestFactory->create()->load($chatModel->getRmaRequestId());
                $vendor_info = $this->vendorFactory->create()->load($requestModel->getVendorId());
                $customer_info = ['name' => $vendor_info->getName(), 'email' => $vendor_info->getEmail()];
                if (!empty($scopeConfig->getValue('ced_csmarketplace/rma_general_group/dept_email')) && !empty($scopeConfig->getValue('trans_email/ident_support/name')))
                    array_push($customer_info, ['name' => $scopeConfig->getValue('trans_email/ident_support/name'), 'email' => $scopeConfig->getValue('ced_csmarketplace/rma_general_group/dept_email')]);
                $this->rmaEmailHelper->sendStatusEmail($message, $sender, $customer_info, $storeId);
                return $this->resultRedirectFactory->create()->setUrl($this->_redirect->success($url));

            } catch (\Exception $e) {

                $url = $this->_buildUrl('*/*/view', ['id' => $chatModel->getRmaRequestId()]);

                return $this->resultRedirectFactory->create()->setUrl($this->_redirect->success($url));
                $this->messageManager->addExceptionMessage($e, __('We can\'t submit the message.'));
            }
        }
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        $storeManager = $this->storeManager;
        return $storeManager->getStore()->getId();
    }

}
