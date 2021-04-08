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

use Magento\Store\Model\ScopeInterface;

/**
 * Class Save
 * @package Ced\CsRma\Controller\Customerrma
 */
class Save extends \Ced\CsRma\Controller\Link
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendor;

    /**
     * @var \Ced\CsRma\Helper\OrderDetail
     */
    protected $rmaOrderHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Ced\CsRma\Model\RequestFactory
     */
    protected $rmaRequestFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Ced\CsRma\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $itemFactory;

    /**
     * Save constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Ced\CsRma\Helper\OrderDetail $rmaOrderHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ced\CsRma\Model\RequestFactory $rmaRequestFactory
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsRma\Helper\Email $emailHelper
     * @param \Magento\Sales\Model\Order\ItemFactory $itemFactory
     * @param \Ced\CsRma\Helper\Config $configHelper
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Ced\CsRma\Helper\OrderDetail $rmaOrderHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsRma\Model\RequestFactory $rmaRequestFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsRma\Helper\Email $emailHelper,
        \Magento\Sales\Model\Order\ItemFactory $itemFactory,
        \Ced\CsRma\Helper\Config $configHelper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
    )
    {
        $this->dateTime = $dateTime;
        $this->vendor = $vendor;
        $this->rmaOrderHelper = $rmaOrderHelper;
        $this->scopeConfig = $scopeConfig;
        $this->rmaRequestFactory = $rmaRequestFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_redirect = $context->getRedirect();
        $this->_url = $context->getUrl();
        $this->customerFactory = $customerFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->emailHelper = $emailHelper;
        $this->itemFactory = $itemFactory;
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

        $data = $this->getRequest()->getParams();

        $customerId = $this->_customerSession->getCustomer()->getId();
        $customer = $this->customerFactory->create()->load($customerId);
        $customerEmail = $customer->getEmail();
        $adminEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
        $customerName = $customer->getName();
        if ($data['item-data']['item-id']) {
            try {

                /*make an array to save data*/
                $rmaData = [];

                foreach ($data['item-data']['item-id'] as $key => $value) {
                    if ($data['item-data']['rma-qty'][$key] == 0) {
                        continue;
                    }

                    $vendorId = $this->vproductsFactory->create()
                        ->getVendorIdByProduct($value);
                    if ($vendorId) {
                        if (isset($rmaData[$vendorId])) {
                            $rmaData[$vendorId]['item'][] = $this->getRmaItemDetail($data['item-data'], $key, $vendorId);
                        } else {
                            $rmaData[$vendorId] = $this->rmaOrderHelper->getRequestDetailsOrder($data, $vendorId);
                            $rmaData[$vendorId]['vendor_id'] = $vendorId;
                            $rmaData[$vendorId]['item'][] = $this->getRmaItemDetail($data['item-data'], $key, $vendorId);
                        }
                    } else {
                        if (isset($rmaData['admin'])) {
                            $rmaData['admin']['item'][] = $this->getRmaItemDetail($data['item-data'], $key, 'admin');
                        } else {
                            $vendorId = '0';
                            $rmaData['admin'] = $this->rmaOrderHelper->getRequestDetailsOrder($data, $vendorId);
                            $rmaData['admin']['vendor_id'] = 'admin';
                            $rmaData['admin']['item'][] = $this->getRmaItemDetail($data['item-data'], $key, 'admin');
                        }
                    }
                }

                foreach ($rmaData as $rma_key => $rma_data) {
                    /*create factory for rma request and rma shipping */
                    $requestModel = $this->rmaRequestFactory->create();
                    $requestModel->addData($rma_data)
                        ->setData('created_at', $this->dateTime->gmtDate())
                        ->setData('updated_at', $this->dateTime->gmtDate())
                        ->save();
                }
                $vendorUrl = $this->_url->getUrl('csrma/vrma/edit', ['rma_id' => $requestModel->getRmaRequestId()]);
                $adminUrl = $this->_url->getUrl('csrma/allrma/edit', ['id' => $requestModel->getRmaRequestId()]);
                $url = $this->_url->getUrl('csrma/customerrma/view', ['id' => $requestModel->getRmaRequestId()]);
                $this->emailHelper->sendNewRmaMail($data, $customerEmail, $customerName, $requestModel->getRmaId(), $url, 'customer_mail_template');
                $this->emailHelper->sendNewRmaMail($data, $adminEmail, $customerName, $requestModel->getRmaId(), $adminUrl, 'admin_new_mail_template');
                foreach ($data['item-data']['item-id'] as $key => $value) {

                    if ($data['item-data']['rma-qty'][$key] == 0) {
                        continue;
                    }

                    $vendorId = $this->vproductsFactory->create()
                        ->getVendorIdByProduct($value);
                    $vendor = $this->vendor->create()->load($vendorId);
                    $vendorEmail = $vendor->getEmail();
                    if ($vendorId) {
                        $this->emailHelper->sendNewRmaMail($data, $vendorEmail, $customerName, $requestModel->getRmaId(), $vendorUrl, 'admin_new_mail_template');
                    }
                }

                $this->messageManager->addSuccessMessage(__('Your request has been submitted.'));
                $url = $this->_buildUrl('*/*/index', ['_secure' => true]);
                return $this->resultRedirectFactory->create()
                    ->setUrl($this->_redirect->success($url));
            } catch (\Exception $e) {
                $redirectUrl = $this->_buildUrl('*/*/index');
                $this->messageManager->addExceptionMessage($e, __('We can\'t save the address.'));
            }
        }
        $redirectUrl = $this->_buildUrl('*/*/index');
    }

    /**
     * @param $item
     * @param $key
     * @param $vendorId
     * @return array
     */
    protected function getRmaItemDetail($item, $key, $vendorId)
    {

        $itemDetails = $this->itemFactory->create()->load($item['order_item_id'][$key]);
        $rowTotal = ($itemDetails->getRowTotalInclTax() / $itemDetails->getQtyOrdered()) * $item['rma-qty'][$key];
        if ($itemDetails->getDiscountAmount()) {
            $perquantitydiscount = $itemDetails->getDiscountAmount() / $itemDetails->getQtyOrdered();
        }
        $rowTotal = $rowTotal - ($perquantitydiscount * $item['rma-qty'][$key]);
        $itemData = [
            'product_id' => $item['item-id'][$key],
            'sku' => $item['item-sku'][$key],
            'item_name' => $item['item-name'][$key],
            'price' => $item['item-price'][$key],
            'rma_qty' => $item['rma-qty'][$key],
            'row_total' => $rowTotal,
            'vendor_id' => $vendorId,
            'sales_item_id' => $item['order_item_id'][$key]
        ];
        return $itemData;
    }
}

