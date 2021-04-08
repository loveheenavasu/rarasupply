<?php

namespace Ced\CsMultiShipping\Plugin;

class Order
{
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Framework\App\Request\Http $request
    ) {
        
        $this->scopeConfig = $scopeConfig;
        $this->orderCollection = $orderCollection;
        $this->shipmentRepository = $shipmentRepository;
        $this->request = $request;
    }

    public function aroundGetShippingMethod(
        \Magento\Sales\Model\Order $subject,
        \Closure $proceed,
        $asObject = false
    )
    {
        $shipmentId = $this->request->getParam('shipment_id');
        if($shipmentId){
            $shipmentData = $this->shipmentRepository->get($shipmentId);
            $orderCollectionData = $this->orderCollection->create()->addFieldToFilter('entity_id',$shipmentData->getOrderId());
           $shippingMethod = $orderCollectionData->getFirstItem()->getData('shipping_method');
           $shippingMethod = str_replace('vendor_rates_', '', $shippingMethod);
           $shippingMethod = str_replace('vendor_rates_', '', $shippingMethod);
           if(strpos($shippingMethod, '~')!==false){
                $shippingMethod = explode('~', $shippingMethod);
                $shippingMethod = $shippingMethod[0];
           }
           $shippingMethodExplode = explode(':',$shippingMethod);
           

           if(isset($shippingMethodExplode[0]))
                $shippingMethod = $shippingMethodExplode[0];
            $isMultishippingEnable = $this->scopeConfig->getValue('ced_csmultishipping/general/activation',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            //print_r($shippingMethod);die;
           // $shippingMethod = "fedex_FEDEX_GROUND";
            if($isMultishippingEnable){
                if (!$asObject || !$shippingMethod) { //echo $shippingMethod;die('=-=-=');
                    return $shippingMethod;
                } else {
                    //echo $shippingMethod;die('=-=in else-=');
                    list($carrierCode, $method) = explode('_', $shippingMethod, 2);
                    return new \Magento\Framework\DataObject(['carrier_code' => $carrierCode, 'method' => $method]);
                }
            }
        }

        $shippingDetails = $proceed($asObject);

            return $shippingDetails;
    }
}