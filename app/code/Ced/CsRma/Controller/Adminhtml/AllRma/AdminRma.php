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

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AdminRma
 * @package Ced\CsRma\Controller\Adminhtml\AllRma
 */
class AdminRma extends \Magento\Backend\App\Action
{

    /**
     * @var \Ced\CsRma\Helper\Data
     */
    public $rmaDataHelper;

    /**
     * @var \Ced\CsRma\Model\RequestFactory
     */
    protected $rmaRequestFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $dateTime;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Ced\CsRma\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var \Magento\Backend\Block\Page\Header
     */
    protected $header;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $itemFactory;

    /**
     * AdminRma constructor.
     * @param \Ced\CsRma\Helper\Data $rmaDataHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Ced\CsRma\Model\RequestFactory $rmaRequestFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ced\CsRma\Helper\Email $emailHelper
     * @param \Magento\Backend\Block\Page\Header $header
     * @param \Magento\Sales\Model\Order\ItemFactory $itemFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Ced\CsRma\Helper\Data $rmaDataHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Ced\CsRma\Model\RequestFactory $rmaRequestFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\CsRma\Helper\Email $emailHelper,
        \Magento\Backend\Block\Page\Header $header,
        \Magento\Sales\Model\Order\ItemFactory $itemFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->rmaDataHelper = $rmaDataHelper;
        $this->dateTime = $dateTime;
        $this->rmaRequestFactory = $rmaRequestFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->vendorFactory = $vendorFactory;
        $this->storeManager = $storeManager;
        $this->emailHelper = $emailHelper;
        $this->header = $header;
        $this->itemFactory = $itemFactory;
        parent::__construct($context);
    }

    /**
     * Customer rma form  in adminpanel action
     * @return void|\Magento\Framework\Controller\Result\Page
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            try {
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
                            $rmaData[$vendorId] = $this->getAdminRequestDetails($data, $vendorId);
                            $rmaData[$vendorId]['vendor_id'] = $vendorId;
                            $rmaData[$vendorId]['item'][] = $this->getRmaItemDetail($data['item-data'], $key, $vendorId);
                        }
                    } else {
                        if (isset($rmaData['admin'])) {
                            $rmaData['admin']['item'][] = $this->getRmaItemDetail($data['item-data'], $key, 'admin');
                        } else {
                            $vendorId = '0';
                            $rmaData['admin'] = $this->getAdminRequestDetails($data, $vendorId);
                            $rmaData['admin']['vendor_id'] = 'admin';
                            $rmaData['admin']['item'][] = $this->getRmaItemDetail($data['item-data'], $key, 'admin');
                        }
                    }
                }
                foreach ($rmaData as $rma_key => $rma_data) {
                    $requestModel = $this->rmaRequestFactory->create();
                    $requestModel->addData($rma_data)->save();
                    if ($rma_key != 'admin') {
                        $vendor = $this->vendorFactory->create()->load($rma_key);
                        $url = $this->storeManager->getStore()->getBaseUrl() . 'csrma/vrma/edit/rma_id/' . $requestModel->getRmaRequestId();
                        $this->emailHelper->sendNewRmaMail($data, $vendor->getEmail(), $data['user-name'], $requestModel->getRmaId(), $url, 'admin_new_mail_template');
                    }
                }
                $url = $this->header->getBaseUrl() . 'csrma/customerrma/view/id/' . $requestModel->getRmaRequestId();
                $this->emailHelper->sendNewRmaMail($data, $data['email'], $data['user-name'], $requestModel->getRmaId(), $url, 'customer_mail_template');
                $this->messageManager->addSuccessMessage(__('You submitted the request.'));
                return $resultRedirect->setPath('csrma/*/');

            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving this request.'));
            }
        }
        $resultRedirect->setPath('csrma/*/');
        return $resultRedirect;
    }

    /**
     * get Admin Rma Request Detail
     *
     * @return array
     */
    protected function getAdminRequestDetails($data, $vendorId)
    {
        return $setdata = [
            'order_id' => $data['order_id'],
            'rma_id' => $this->rmaDataHelper->getRmaID($data['order_id'], $vendorId),
            'status' => $data['status'],
            'resolution_requested' => $data['resolution_requested'],
            'package_condition' => $data['package_condition'],
            'reason' => $data['reason'],
            'customer_name' => $data['user-name'],
            'customer_email' => $data['email'],
            'store_id' => $data['store_id'],
            'website_id' => $data['website_id'],
            'customer_id' => $data['customer_id'],
            'external_link' => $this->rmaDataHelper->getExternalLink(),
            'created_at' => $this->dateTime->gmtDate(),
            'updated_at' => $this->dateTime->gmtDate()
        ];
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
        $itemData = [];
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



