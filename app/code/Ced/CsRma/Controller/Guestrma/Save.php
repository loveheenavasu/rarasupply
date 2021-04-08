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

namespace Ced\CsRma\Controller\Guestrma;

/**
 * Class Save
 * @package Ced\CsRma\Controller\Guestrma
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var  \Ced\CsRma\Helper\OrderDetail
     */
    protected $rmaOrderHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var redirect
     */
    protected $_redirect;

    /**
     * @var \Ced\CsRma\Model\RequestFactory
     */
    protected $rmaRequestFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * Save constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Ced\CsRma\Model\RequestFactory $rmaRequestFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Ced\CsRma\Helper\OrderDetail $rmaOrderHelper
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Sales\Model\Order\ItemFactory $itemFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Ced\CsRma\Model\RequestFactory $rmaRequestFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Ced\CsRma\Helper\OrderDetail $rmaOrderHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Sales\Model\Order\ItemFactory $itemFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
    )
    {
        $this->dateTime = $dateTime;
        $this->rmaOrderHelper = $rmaOrderHelper;
        $this->rmaRequestFactory = $rmaRequestFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_redirect = $context->getRedirect();
        $this->urlBuilder = $urlInterface;
        $this->itemFactory = $itemFactory;
        $this->vproductsFactory = $vproductsFactory;
        parent::__construct($context);

    }

    /**
     * @param execute
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/form');
        }
        $data = $this->getRequest()->getPostValue();
        if ($data) {

            $rmaData = [];
            try {
                foreach ($data['item-data']['item-id'] as $key => $value) {
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
                    $requestModel = $this->rmaRequestFactory->create();
                    $requestModel->addData($rma_data)
                        ->setData('created_at', $this->dateTime->gmtDate())
                        ->setData('updated_at', $this->dateTime->gmtDate())
                        ->save();
                }
                $url = $this->_buildUrl('*/*/form', ['_secure' => true, 'id' => $requestModel->getRmaRequestId()]);
                $this->messageManager->addSuccessMessage(__('Your request has been submitted.'));
                return $this->resultRedirectFactory->create()
                    ->setUrl($this->_redirect->success($url));

            } catch (Exception $e) {
                $redirectUrl = $this->_buildUrl('csrma/guestrma/form');
                $this->messageManager->addExceptionMessage($e, __('We can\'t save.'));
            }
        }
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _buildUrl($route = '', $params = [])
    {
        /** @var \Magento\Framework\UrlInterface $urlBuilder */
        $urlBuilder = $this->urlBuilder;
        return $urlBuilder->getUrl($route, $params);
    }

    /**
     * @param $item
     * @param $key
     * @param $vendorId
     * @return array
     */
    protected function getRmaItemDetail($item, $key, $vendorId)
    {
        $itemDetails = $this->itemFactory->create()->load($item['order-item-id'][$key]);
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
            'sales_item_id' => $item['order-item-id'][$key]
        ];
        return $itemData;
    }
}
