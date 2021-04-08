<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\CsFedexShipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
class SalesShipment implements ObserverInterface
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
     protected $_registry = null;

      protected $_code = '';

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote $quote
     */
   public function __construct (  
       \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Fedex\Model\Carrier $shippingFedex,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager, 
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list

    ) { 
        $this->_request = $request;
        $this->_objectManager=$objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->_shippingFedex = $shippingFedex; 
        $this->messageManager = $messageManager;
        $this->_registry = $registry;
        $this->directory_list = $directory_list;
    }
 

    /**
     * When applying a catalog price rule, make related quotes recollect on demand
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {      
        $invoice = $observer->getEvent()->getInvoice();
        $shipment = $observer->getEvent()->getShipment(); 
        $order = $shipment->getOrder();
        $shippingMethod = $order->getShippingMethod();
        $payment =  $order->getPayment()->getMethodInstance()->getCode();
        $postData = $this->_request->getPostValue();
        try
        {   $flag = false;
            if(isset($postData['tracking']) && count($postData['tracking'])>0 && $postData['tracking'][1]['carrier_code']=='fedex'){
                $flag = true;
                
            }
            if(strpos($shippingMethod, 'fedex') !== false || $flag)
            {
                $response = $this->_objectManager->create('Ced\CsFedexShipping\Model\Pickupcall')->getpickup($shippingMethod,$shipment); 
                //die('---');
                //print_r($response);die;
                //print_r($response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['AssociatedShipments']['Label']['Parts']['Image']);die;
                $directory = $this->_objectManager->get('\Magento\Framework\Filesystem\DirectoryList');

                $path = $directory->getRoot().'/fedex/'; 
                if (!file_exists($path))
                { 
                    mkdir($path, 0777, true);
                }
                $result = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['CompletedPackageDetails']['Label']['Parts']['Image'];
                $trackingNumber = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['CompletedPackageDetails']['TrackingIds']['TrackingNumber'];

                $file = $trackingNumber.'.pdf';
                $idupload = fopen($path.$file, 'w'); 
                fwrite($idupload, base64_decode($result));          
                /*header("content-type: application/pdf");
                header("Content-Disposition:inline;filename=$order_id.pdf");
                print_r(base64_decode($result));die;*/   
                if($payment=='cashondelivery'){
                    $codTrack = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['AssociatedShipments']['TrackingId']['TrackingNumber'];
                    $filename = $codTrack.'.pdf';
                    $codLabel = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['AssociatedShipments']['Label']['Parts']['Image'];
                    $codupload = fopen($path.$filename, 'w');   
                    fwrite($codupload, base64_decode($codLabel));
                }
                //die('===');
                /*$rout = get_object_vars(json_decode(json_encode($response->Body->ProcessShipmentReply->CompletedShipmentDetail->OperationalDetail->UrsaSuffixCode)));
                $airportId = get_object_vars(json_decode(json_encode($response->Body->ProcessShipmentReply->CompletedShipmentDetail->OperationalDetail->AirportId)));
                $serviceArea = get_object_vars(json_decode(json_encode($response->Body->ProcessShipmentReply->CompletedShipmentDetail->OperationalDetail->DestinationServiceArea)));
                $prefix = get_object_vars(json_decode(json_encode($response->Body->ProcessShipmentReply->CompletedShipmentDetail->OperationalDetail->UrsaPrefixCode)));
                $postalcode = get_object_vars(json_decode(json_encode($response->Body->ProcessShipmentReply->CompletedShipmentDetail->OperationalDetail->PostalCode)));          
                $formid = get_object_vars(json_decode(json_encode($response->Body->ProcessShipmentReply->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->FormId)));
                */

                /*$data['barcode'] = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['CompletedPackageDetails']['OperationalDetail']['Barcodes']['StringBarcodes']['Value'];
                $data['form_id'] = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['CompletedPackageDetails']['TrackingIds']['FormId'];
                $data['service_area'] = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['OperationalDetail']['DestinationServiceArea'];
                $data['prefix'] = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['OperationalDetail']['UrsaPrefixCode'];
                $data['rout_code'] = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['OperationalDetail']['UrsaSuffixCode'];
                $data['airport_id'] = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['OperationalDetail']['AirportId'];
                $data['postal_code'] = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['OperationalDetail']['PostalCode'];
                if($payment=='cashondelivery'){             
                    $data['cod']['form_id'] = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['AssociatedShipments']['TrackingId']['FormId'];
                    $data['cod']['awb'] = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['CompletedPackageDetails']['TrackingIds']['TrackingNumber'];
                    $data['cod']['barcode'] = $response['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['AssociatedShipments']['PackageOperationalDetail']['Barcodes']['StringBarcodes']['Value'];
                }*/

                $shipment = $observer->getEvent()->getShipment();
                $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\Track')
                            ->setNumber($trackingNumber) 
                            ->setCarrierCode('fedex') 
                            ->setTitle('FEDEX')
                            ->setFedexDetail($codTrack);
                $shipment->addTrack($track);
            }
            
        }catch(\Exception $e){ //echo $e->getMessage();die('---in catch--');
           throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }                       
    }
}



