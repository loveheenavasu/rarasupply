<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsRma\Controller\Adminhtml\AllRma;

use Magento\Backend\App\Action\Context;
use Magento\Framework\DataObject;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Ced\CsRma\Model\RequestFactory;
use Ced\CsRma\Helper\Data;



class JsonInfo extends \Magento\Backend\App\Action
{
     /**
    * @var Magento\Sales\Model\OrderFactory
    */

    protected $orderAddressRepository;
    /**
    * @var Ced\CsRma\Helper\Data
    */

    protected $rmaDataHelper;
    /**
    * @var \Ced\CsRma\Model\RequestFactory
    */

    protected $rmaRequestFactory;
    /**
     * Group service
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

	 /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var \Ced\CsRma\Helper\OrderDetail
     */
    protected $rmaOrderHelper;


	/**
     * @param \Magento\Backend\App\Action\Context $context
     * @param Ced\CsRma\Helper\Data $rmaDataHelper
     * @param Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param Ced\CsRma\Model\RequestFactory $rmaRequestFactory
     */
    public function __construct(
        Context $context,
        Data $rmaDataHelper,
        GroupRepositoryInterface $groupRepository,
        OrderAddressRepositoryInterface $orderAddressRepository,
        OrderRepositoryInterface $orderRepository,
        \Ced\CsRma\Helper\OrderDetail $rmaOrderHelper,
        RequestFactory $rmaRequestFactory
    ) {
        $this->rmaOrderHelper = $rmaOrderHelper;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->rmaDataHelper = $rmaDataHelper;
        $this->rmaRequestFactory = $rmaRequestFactory;
        $this->groupRepository = $groupRepository;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
    	$response = new DataObject();
        $resolution ='';
    	$id = $this->getRequest()->getParam('id');
    	if (intval($id) > 0) {
            $order = $this->orderRepository->get($id);

            $resolutions = $this->getResolutionForOrder($id);
            foreach ($resolutions as $resolution_key =>$resolution_value) {
                $resolution .= '<option value="'.$resolution_key.'">'.$resolution_value.'</option>';
            }

            /*get group id*/
            $group = $this->groupRepository->getById($order
                        ->getCustomerGroupId())->getCode();
            $htmlcontent = '';
            if (is_array($order->getData())) {
                foreach ($order->getAllVisibleItems() as $_item) {
                    $itemList = $this->rmaOrderHelper->getItemsList($_item,$order->getIncrementId());
                    $store = $order->getStoreId();
                    if($itemList){
                        $itemPrice = $order->getOrderCurrencyCode() .$itemList['price'];
                        //$itemPrice = $this->rmaDataHelper->getPriceByStore($itemList['price'],$store);
                        $htmlcontent .= '<tr class="item-list">';
                        $htmlcontent .= '<td>'.$itemList['name'].'</td>';
                        $htmlcontent .= '<td>'.$itemList['sku'].'</td>';
                        $htmlcontent .= '<td>'.$itemPrice.'</td>';
                        $htmlcontent .= '<td>'.intval($itemList['qty']).'</td>';
                        $htmlcontent .= '<td>'.$order->getOrderCurrencyCode() .$itemList['row_total_incl_tax'].'</td>';
                        //$htmlcontent .= '<td>'.$this->rmaDataHelper->getPriceByStore($itemList['row_total_incl_tax'],$store).'</td>';
                        $htmlcontent .= '<td>';
                        $htmlcontent .= '<input type="hidden" value="'.$itemList['product_id'].'" name="item-data[item-id][]">';
                        $htmlcontent .= '<input type="hidden" value="'.$itemList['item_id'].'" name="item-data[order_item_id][]">';
                        $htmlcontent .= '<input type="hidden" value="'.$itemList['sku'].'" name="item-data[item-sku][]">';
                        $htmlcontent .= '<input  type="hidden" value="'.$itemList['name'].'" name="item-data[item-name][]">';
                        $htmlcontent .= '<input  type="hidden" value="'.$itemList['price'].'" name="item-data[item-price][]">';
                        $htmlcontent .= '<input type="hidden" value="'.$itemList['qty'].'" name="item-data[item-ordered][]">';
                        $htmlcontent .= '<input  type="hidden" value="'.$itemList['row_total_incl_tax'].'" name="item-data[item-row-total][]">';
                        $htmlcontent .= '<input type="text"  value="'.intval($itemList['qty']).'" id="rma-qty" name="item-data[rma-qty][]" class="input-text admin__control-text qty-input" oninput="validateQty(this,'.intval($itemList['qty']).')">';
                        $htmlcontent .='</td>';
                        $htmlcontent .= '</tr>';
                    }
                }
            }
            $addressValue = $this->getAddress($order);
            $store = $this->rmaDataHelper->getStoreValue($order->getStoreId());
            $response->setId($id);
            $response->setResolution($resolution);
            $response->addData($order->getData());
            $response->setGroup($group);
            $response->setStore($store);
            $response->setProductData($htmlcontent);
            $response->setAddress($addressValue);
            $response->setError(0);

        } else {
                $response->setError(1);
                $response->setMessage(__('We can\'t retrieve the Order ID.'));
            }
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($response->toArray());
            return $resultJson;
    }

    /*protected function isRmaExist($id)
    {
        $rmaRequest = $this->rmaRequestFactory->create()
                    ->addFieldToFilter('order_id',$id)
                    ->getData('rma_request_id');
        return $rmaRequest
        $htmlcontent.= '<input type="button"  class="update-rma-item" id="update-rma-item-'.$_item->getProductId().'"  value ="Update"name="update-rma">';
    }*/

    /**
     * get address info for guest/customer
     *
     * @return void|\Magento\Framework\Controller\Result\Page
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getAddress($order)
    {
        $address =[];
        if($order->getData('customer_is_guest')==1)
        {
            $address['billing_address'] = $this->rmaDataHelper->format($order
                        ->getBillingAddress(), 'html');
            $address['shipping_address'] = $this->rmaDataHelper->format($order
                        ->getShippingAddress(), 'html');

            $address['customer_name'] = $order->getBillingAddress()->getFirstname()
                        .$order->getBillingAddress()->getLastname();
            $address['customer_email'] = $order->getBillingAddress()->getEmail();

        } else {
            $billingAddressId = $order->getBillingAddressId();
            $shippingAddressId = $order->getShippingAddressId();

            if($shippingAddressId){
                $shippingAddress = $this->orderAddressRepository->get($shippingAddressId);
                $address['shipping_address'] = $this->rmaDataHelper->format($shippingAddress,'html');
            }

            if($billingAddressId) {
                $billingAddress = $this->orderAddressRepository->get($billingAddressId);
                $address['billing_address'] = $this->rmaDataHelper->format($billingAddress,'html');
            }
            $address['customer_email']= $order->getCustomerEmail();
            $address['customer_name'] = $order->getCustomerFirstname().
                    $order->getCustomerLastname();
        }
        return $address;
    }

    public function getResolutionForOrder($orderId)
    {
        $resolution_def_array = array(''=>'Please select a resolution');
        $resolutions_updated = $this->rmaOrderHelper->getAvailableResolutionsForOrder($orderId);
        $resolutions = array_merge($resolution_def_array,$resolutions_updated);
        return $resolutions;
    }
}

