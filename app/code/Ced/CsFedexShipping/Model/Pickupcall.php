<?php 

/**
 * CedCommerce
  *
  * NOTICE OF LICENSE
  *
  * This source file is subject to the Academic Free License (AFL 3.0)
  * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
  * It is also available through the world-wide-web at this URL:
  * http://opensource.org/licenses/afl-3.0.php
  *
  * @category    Ced
  * @package     Ced_CsFedexShipping
  * @author       CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */

namespace Ced\CsFedexShipping\Model;

use Magento\Framework\Module\Dir;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Xml\Security;

class PickupCall extends \Magento\Framework\Model\AbstractModel
{
     

     public function __construct (        
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Framework\Message\ManagerInterface $messageManager  
    ) { 
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
    }
    

	public function getpickup($method,$shipment)
	{
		//print_r($method);die;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();  
		$order =$objectManager->create('Magento\Sales\Model\Order')->load($shipment->getOrderId());
    	$address = $order->getShippingAddress();
		$vendorId = $objectManager->get('Magento\Customer\Model\Session')->getVendorId();
		
		$vendor = $objectManager->get('Ced\CsMarketplace\Model\Vendor')->load($vendorId);

		$vendor_order =$objectManager->get('Ced\CsMarketplace\Model\Vorders')->load($shipment->getOrderId());
		$shipper_mobile = $vendor->getContactNumber();
		if(!$shipper_mobile)
			$shipper_mobile = $vendor->getContact();
		
		
		$items = $objectManager->get("\Magento\Sales\Model\Order\Item")->getCollection()->addFieldToFilter('order_id',$order->getEntityId())->addFieldToFilter('vendor_id',$vendorId);
    	
    	$item['name'] = '';
		$item['qty'] = 0;
		//$item['price'] = 0;
		$item['weight'] = 0;
		foreach($items as $itemId)
		{
			//print_r($itemId->getData());
			$item['name'] .= (!$item['name']) ? $itemId->getName() : ' '.$itemId->getName();		
			$item['weight'] = $item['weight'] + $itemId->getRowWeight();
			$item['qty'] = $item['qty'] + $itemId->getQtyOrdered();
		}
		if(!$item['weight'])
			$item['weight'] = 1;
		$pickups_data = array(
	                    'Shipper_Contact_PersonName' => $vendor->getPublicName(), //Optional. Identifies the contact person's name.                    
	                    'CustomerReferences' => 'tx01',
	                    'Shipper_Address_CompanyName' => $vendor->getCompanyName(), //Optional.Identifies the company this contact is associated with.   
	                    'Shipper_Address_StreetLines' => $vendor->getAddress(), //Max 35 charater  //Combination of number, street name, etc. At least one line is required for a valid physical address; empty lines should not be included.                    
	                    'Shipper_Address_StreetLines1' => $vendor->getAddress(), //Max 35 charater  //Combination of number, street name, etc. At least one line is required for a valid physical address; empty lines should not be included.
	                    'Shipper_Address_City' => $vendor->getCity(), //Name of city, town, etc.                    
	                    'Shipper_Address_State' =>$objectManager->get('Magento\Directory\Model\Region')->load($address->getRegionId())->getName(),  //Identifying abbreviations for India state.                    
	                    'Shipper_Address_PostalCode' => $vendor->getZipCode(), //Identification of a region (usually small) for mail/package delivery                   
	                    'Shipper_Contact_PhoneNumber' => $shipper_mobile,  //Identifies the phone number associated with this contact f the search criterion is PHONE_NUMBER. Numeric value only, for example 9015551234. Mobile numbers will not return results.                    
	                    'Shipper_Contact_Email' => $vendor->getEmail(), //Identifies the email address associated with this contact.
	                    'Shipper_Country_id'=>$vendor->getCountryId(),
	                    //get a country id 

	                    'Recipient_Contact_PersonName' => $address->getFirstname(),
	                    'Recipient_Address_CompanyName' => $address->getCompany(),
	                    'Recipient_Address_StreetLines' => $address['street'], //Max 35 charater  //Combination of number, street name, etc. At least one line is required for a valid physical address; empty lines should not be included.
	                    'Recipient_Address_StreetLines1' => $address['street'], //Max 35 charater //Combination of number, street name, etc. At least one line is required for a valid physical address; empty lines should not be included.
	                    'Recipient_Address_City' => $address->getCity(), //Name of city, town, etc.     
	                    'Recipient_Address_State' =>  $objectManager->get('Magento\Directory\Model\Region')->load($address->getRegionId())->getName(), //Identifying abbreviations for India state.     
	                    'Recipient_Address_PostalCode' => $address->getPostcode(), //Identification of a region (usually small) for mail/package delivery
	                    'Recipient_Contact_PhoneNumber' => $address->getTelephone(), //Identifies the phone number associated with this contact f the search criterion is PHONE_NUMBER. Numeric value only, for example 9015551234. Mobile numbers will not return results.
	                    'Recipient_Country_id'=>$address->getCountryId(),
	                    //get country id of Recipient data 
	                    'Commodities_Description' => $item['name'],  //Complete and accurate description of the commodity.                    
	                    'RequestedPackageLineItems_Weight' => $item['weight'], //Specify weight of commodity.                    
	                    'RequestedPackageLineItems_Dimensions_Length' => '1',  //Optional. Submitted in the Ship request on the package level.                    
	                    'RequestedPackageLineItems_Dimensions_Width' => '1',  //Optional. Submitted in the Ship request on the package level.
	                    'RequestedPackageLineItems_Dimensions_Height' => '1',  //Optional. Submitted in the Ship request on the package level.
	                    'Commodities_CustomsValue' => $vendor_order->getOrderTotal(),
	                    'pickup_total_weight'=> $item['weight'],
	                    'Quantity'=> (int)$item['qty'],
	                    'CODvalue' => $vendor_order->getOrderTotal(),  //Optional.The amount to be collected for the shipment.
                    	'CODPrePaid' => $order->getPayment()->getMethodInstance()->getCode(), 
	                );	
		
		function get_paramater($arg1=''){
			$keys= 'shipbazar';
			$reg_data = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($keys), base64_decode($arg1), MCRYPT_MODE_CBC, md5(md5($keys))), "\0");
			return $reg_data;
		}
		$one_value = $pickups_data;
		date_default_timezone_set("Asia/Kolkata");	
		$doc_typed = "NON_DOCUMENTS";
		$ServiceType = 'FEDEX_EXPRESS';//get_paramater('HFMDaiRjy4HVfu2qZRITgIQrHxcaQfs+2s//fhYuYSk='/*,$order->getStoreName()*/);
		
		//if(strpos($method,'STANDARD_OVER')!==false)
			$ServiceType = 'STANDARD_OVERNIGHT';
		//echo $method;die;
		$Purpose = 'SOLD';
		$shipamount = (int)$order->getShippingAmount();
		$shipamount = $one_value['Commodities_CustomsValue'];
		$CustomerTransactionId = "SQ_".date("Y_m_d_h_i_s");
		
		$CustomerReferenceType="SQ_".$one_value['CustomerReferences'];
		//echo $CustomerReferenceType;die('==='); 
		$domesticfrom_city = $one_value['Shipper_Address_State'];
		$domesticto_city = $one_value['Recipient_Address_State'];
       
		$StateOrProvinceCode_array=array("AN"=> "andaman","AN"=> "nicobar","AN"=> "islands","AP"=> "andhrapradesh","AR"=> "arunachalpradesh","AS"=> "assam","BR"=> "bihar","CH"=> "chandigarh","CT"=> "chhattisgarh","DN"=> "dadraandnagarhaveli","DD"=> "damananddiu","DL"=> "delhi","GA"=> "goa","GJ"=> "gujarat","HR"=> "haryana","HP"=> "himachalpradesh","JK"=> "jammuandkashmir","JH"=> "jharkhand","KA"=> "karnataka","KL"=> "kerala","LD"=> "lakshadweep","MP"=> "madhyapradesh","MH"=> "maharashtra","MN"=> "manipur","ML"=> "meghalaya","MZ"=> "mizoram","NL"=> "nagaland","OR"=> "odisha","PY"=> "puducherry","PB"=> "punjab","RJ"=> "rajasthan","SK"=> "sikkim","TN"=> "tamilnadu","TG"=> "telangana","TR"=> "tripura","UT"=> "uttarakhand","UP"=> "uttarpradesh","WB"=> "westbengal");
		$domesticfrom_city1=str_replace("&", "and", strtolower($domesticfrom_city));
		$domesticfrom_city1=str_replace("amp;", "", strtolower($domesticfrom_city1));
		$domesticfrom_city1=str_replace(" ", "", strtolower($domesticfrom_city1));
		$StateOrProvinceCode_from=array_search($domesticfrom_city1,$StateOrProvinceCode_array);
		
		$domesticto_city1=str_replace("&", "and", strtolower($domesticto_city));
		$domesticto_city1=str_replace("amp;", "", strtolower($domesticto_city1));
		$domesticto_city1=str_replace(" ", "", strtolower($domesticto_city1));	
		$StateOrProvinceCode_to=array_search($domesticto_city1,$StateOrProvinceCode_array);
		//print_r($pickups_data);die('hiiii');
		$StateOrProvinceCode_to="AK";
		$StateOrProvinceCode_from="UP";
		$BillingAddress_str='<StreetLines>'.$one_value['Shipper_Address_StreetLines'].'</StreetLines>';
		if($one_value['Shipper_Address_StreetLines1']!=""){
			$BillingAddress_str.='<StreetLines>'.$one_value['Shipper_Address_StreetLines1'].'</StreetLines>';
		}	
		$ShippingAddress_str='<StreetLines>'.$one_value['Recipient_Address_StreetLines'].'</StreetLines>';
		if($one_value['Recipient_Address_StreetLines1']!=""){
			$ShippingAddress_str.='<StreetLines>'.$one_value['Recipient_Address_StreetLines1'].'</StreetLines>';
		}
		$Quantity=$one_value['Quantity'];
		$arg1='NidKUys7a8GbOkpk8OqMEbmdnY0JffZffb7Yh+3YQag=';
		$sh_string=get_paramater($arg1);	
			$today_date_m=date("Y-m-d H:i:s");
			$today_hour=date("H",strtotime($today_date_m));
			$today_min=date("i",strtotime($today_date_m));
			$today_date=date("Y-m-d",strtotime($today_date_m));	
			$pickupt_date=date("Y-m-d",strtotime($today_date_m));
			$pickup_hour=$today_hour;
			$pickup_min=$today_min;
			$key = $this->scopeConfig->getValue('carriers/fedex/key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$pass =$this->scopeConfig->getValue('carriers/fedex/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$meter =$this->scopeConfig->getValue('carriers/fedex/meter_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$account =$this->scopeConfig->getValue('carriers/fedex/account', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
			$mode = $this->scopeConfig->getValue('carriers/fedex/sandbox_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			$url = 'https://ws.fedex.com:443/web-services';
			if($mode)
				$url = 'https://wsbeta.fedex.com:443/web-services';
			
	 		 
			 //$key = '7idCpv4CsBsemCyu';
			 //$meter = '118779827';
			 //$pass = '0zOCEY1jkzaXUKCXYfJb72VuA';
			 //$account = '510087720';

			//Mage::getSingleton('core/session')->addSuccess(Mage::helper('csfedexshipping')->__("Status:"));
			/*echo "Status:".$pickup_status."<bR>";
			echo "Pickup Id:".$PickupLocation.$PackingId."<br>";
			echo "Date and Time the package will be ready for pickup:".$pickupfinaltime1."<br>";
			echo "Close Time:".$pickupfinaltime2;
			die('hiii');*/
    

			$domestic_length_ws1=floatval($one_value['RequestedPackageLineItems_Dimensions_Length']);
			$domestic_width_ws1=floatval($one_value['RequestedPackageLineItems_Dimensions_Width']);
			$domestic_height_ws1=floatval($one_value['RequestedPackageLineItems_Dimensions_Height']);
			
			if($domestic_length_ws1==""){$domestic_length_ws1=0;}
			if($domestic_width_ws1==""){$domestic_width_ws1=0;}
			if($domestic_height_ws1==""){$domestic_height_ws1=0;}	
				$pickup_array = $one_value;
				$pickup_array1['StateOrProvinceCode_from']=$StateOrProvinceCode_from;
				$pickup_array1['StateOrProvinceCode_to']=$StateOrProvinceCode_to;
				$pickup_array1['ServiceType']=$ServiceType;				
				$pickup_array1['CustomerTransactionId']=$CustomerTransactionId;				
				$codyes='';
				
				if($one_value['CODPrePaid']=='cashondelivery' && $one_value['CODvalue']!=""){	
				 $codyes='<SpecialServicesRequested>
		               <SpecialServiceTypes>COD</SpecialServiceTypes>
		               <CodDetail>
		                  <CodCollectionAmount>
		                     <Currency>INR</Currency>
		                     <Amount>'.$one_value['CODvalue'].'</Amount>
		                  </CodCollectionAmount>
		                  <CollectionType>CASH</CollectionType>
		               </CodDetail>
		               <DeliveryOnInvoiceAcceptanceDetail>
		                  <Recipient>
		                     <Contact>
				                  <PersonName>'.$one_value['Recipient_Contact_PersonName'].'</PersonName>
				                  <CompanyName>'.$one_value['Recipient_Address_CompanyName'].'</CompanyName>
				                  <PhoneNumber>'.$one_value['Recipient_Contact_PhoneNumber'].'</PhoneNumber>
				               </Contact>
				               <Address>
				                  '.$ShippingAddress_str.'
				                  <City>'.$one_value['Recipient_Address_City'].'</City>
				                  <StateOrProvinceCode>'.$StateOrProvinceCode_to.'</StateOrProvinceCode>
				                  <PostalCode>'.$one_value['Recipient_Address_PostalCode'].'</PostalCode>
				                  <CountryCode>IN</CountryCode>
				               </Address>
		                  </Recipient>
		               </DeliveryOnInvoiceAcceptanceDetail>
		            </SpecialServicesRequested>';
					$ServiceType=$ServiceType;
					$Purpose='SOLD';	
		            }
			$Quantity=$one_value['Quantity'];
			$temp_totalweight = $one_value['pickup_total_weight'];
			$sequence_no=1;
			$arg1='NidKUys7a8GbOkpk8OqMEbmdnY0JffZffb7Yh+3YQag=';
			$sh_string=get_paramater($arg1);
			//print_r($sh_string);die;
			$str_1d='<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
		    <SOAP-ENV:Body>
		      	<ProcessShipmentRequest xmlns="http://'.$sh_string.'.com/ws/ship/v15">
		       <WebAuthenticationDetail>
		            <UserCredential>
		               <Key>'.$key.'</Key>
		               <Password>'.$pass.'</Password>
		            </UserCredential>
		         </WebAuthenticationDetail>
		         <ClientDetail>
		            <AccountNumber>'.$account.'</AccountNumber>
		            <MeterNumber>'.$meter.'</MeterNumber>
		         </ClientDetail>  
		         <TransactionDetail>
		            <CustomerTransactionId>'.$CustomerTransactionId.'</CustomerTransactionId>
		         </TransactionDetail>
		         <Version>
		            <ServiceId>ship</ServiceId>
		            <Major>15</Major>
		            <Intermediate>0</Intermediate>
		            <Minor>0</Minor>
		         </Version>
		         <RequestedShipment>
		            <ShipTimestamp>'.date("Y-m-d").'T'.date("H:i:s").'</ShipTimestamp>
		            <DropoffType>REGULAR_PICKUP</DropoffType>
		            <ServiceType>'.$ServiceType.'</ServiceType>
		            <PackagingType>YOUR_PACKAGING</PackagingType>';
					if($Quantity>1){
		           $str_1d.='<TotalWeight>
		               <Units>KG</Units>
		               <Value>'.$temp_totalweight.'</Value>
		            </TotalWeight>
		             <TotalInsuredValue>
		               <Currency>INR</Currency>
		               <Amount>0</Amount>
		            </TotalInsuredValue>';
		            }
		            $str_1d.='<Shipper>
		               <Contact>
		                  <PersonName>'.$one_value['Shipper_Contact_PersonName'].'</PersonName>
		                  <CompanyName>'.$one_value['Shipper_Address_CompanyName'].'</CompanyName>
		                  <PhoneNumber>'.$one_value['Shipper_Contact_PhoneNumber'].'</PhoneNumber>
		               </Contact>
		               <Address>
		                  '.$BillingAddress_str.'
		                  <City>'.$one_value['Shipper_Address_City'].'</City>
		                  <StateOrProvinceCode>'.$StateOrProvinceCode_from.'</StateOrProvinceCode>
		                  <PostalCode>'.$one_value['Shipper_Address_PostalCode'].'</PostalCode>
		                  <CountryCode>IN</CountryCode>
		                  <Residential>0</Residential>
		               </Address>
		            </Shipper>

		            <Recipient>
		               <Contact>
		                  <PersonName>'.$one_value['Recipient_Contact_PersonName'].'</PersonName>
		                  <CompanyName>'.$one_value['Recipient_Address_CompanyName'].'</CompanyName>
		                  <PhoneNumber>'.$one_value['Recipient_Contact_PhoneNumber'].'</PhoneNumber>
		               </Contact>
		               <Address>
		                  '.$ShippingAddress_str.'
		                  <City>'.$one_value['Recipient_Address_City'].'</City>
		                  <StateOrProvinceCode>'.$StateOrProvinceCode_to.'</StateOrProvinceCode>
		                  <PostalCode>'.$one_value['Recipient_Address_PostalCode'].'</PostalCode>
		                  <CountryCode>IN</CountryCode>
		                  <Residential>0</Residential>
		               </Address>
		            </Recipient>
		            <Origin>
		               <Contact>
		                  <ContactId>orginid</ContactId>
		                  <PersonName>'.$one_value['Shipper_Contact_PersonName'].'</PersonName>
		                  <CompanyName>'.$one_value['Shipper_Address_CompanyName'].'</CompanyName>
		                  <PhoneNumber>'.$one_value['Shipper_Contact_PhoneNumber'].'</PhoneNumber>
		               </Contact>
		               <Address>
		               '.$BillingAddress_str.'
		                  <City>'.$one_value['Shipper_Address_City'].'</City>
		                  <StateOrProvinceCode>'.$StateOrProvinceCode_from.'</StateOrProvinceCode>
		                  <PostalCode>'.$one_value['Shipper_Address_PostalCode'].'</PostalCode>
		                  <CountryCode>IN</CountryCode>
		               </Address>
		            </Origin>
		            <ShippingChargesPayment>
		               <PaymentType>SENDER</PaymentType>
		               <Payor>
		                  <ResponsibleParty>
		                     <AccountNumber>'.$account.'</AccountNumber>
		                     <Contact>
		                        <PersonName>'.$one_value['Shipper_Contact_PersonName'].'</PersonName>
			                    <CompanyName>'.$one_value['Shipper_Address_CompanyName'].'</CompanyName>
			                    <PhoneNumber>'.$one_value['Shipper_Contact_PhoneNumber'].'</PhoneNumber>
		                     </Contact>
		                     <Address>
		                          '.$BillingAddress_str.'
				                  <City>'.$one_value['Shipper_Address_City'].'</City>
				                  <StateOrProvinceCode>'.$StateOrProvinceCode_from.'</StateOrProvinceCode>
				                  <PostalCode>'.$one_value['Shipper_Address_PostalCode'].'</PostalCode>
				                  <CountryCode>IN</CountryCode>
		                        <Residential>0</Residential>
		                     </Address>
		                  </ResponsibleParty>
		               </Payor>
		            </ShippingChargesPayment>'.$codyes.'            
		            <CustomsClearanceDetail>
		               <DutiesPayment>
		                  <PaymentType>SENDER</PaymentType>
		                  <Payor>
		                     <ResponsibleParty>
		                        <AccountNumber>'.$account.'</AccountNumber>
		                        <Contact>
		                           <ContactId/>
		                           <PersonName/>
		                           <CompanyName/>
		                           <PhoneNumber/>
		                           <EMailAddress/>
		                        </Contact>
		                     </ResponsibleParty>
		                  </Payor>
		               </DutiesPayment>
		                <DocumentContent>'.$doc_typed.'</DocumentContent>
		               <CustomsValue>
		                  <Currency>INR</Currency>
		                  <Amount>'.$shipamount.'</Amount>
		               </CustomsValue>
		               <CommercialInvoice>
		               <Purpose>'.$Purpose.'</Purpose>                 
		               </CommercialInvoice>
		               <Commodities>
		                  <NumberOfPieces>'.$Quantity.'</NumberOfPieces>
		                  <Description>'.$one_value['Commodities_Description'].'</Description>
		                  <CountryOfManufacture>IN</CountryOfManufacture>
		                  <Weight>
		                     <Units>KG</Units>
		                     <Value>'.$one_value['RequestedPackageLineItems_Weight'].'</Value>
		                  </Weight>
		                  <Quantity>'.$Quantity.'</Quantity>
		                  <QuantityUnits>CM</QuantityUnits>
		                  <UnitPrice>
		                     <Currency>INR</Currency>
		                     <Amount>'.$shipamount.'</Amount>
		                  </UnitPrice>
		                  <CustomsValue>
		                     <Currency>INR</Currency>
		                     <Amount>'.$shipamount.'</Amount>
		                  </CustomsValue>
		               </Commodities>
		            </CustomsClearanceDetail>

		            <LabelSpecification>
		               <LabelFormatType>COMMON2D</LabelFormatType>
		               <ImageType>PDF</ImageType>
		               <LabelStockType>PAPER_8.5X11_TOP_HALF_LABEL</LabelStockType>
		            </LabelSpecification>
		            <ShippingDocumentSpecification>
		               <ShippingDocumentTypes>PRO_FORMA_INVOICE</ShippingDocumentTypes>
		               <CommercialInvoiceDetail>
		                  <Format>
		                     <ImageType>PDF</ImageType>
		                     <StockType>PAPER_LETTER</StockType>
		                     <ProvideInstructions>1</ProvideInstructions>
		                  </Format>
		               </CommercialInvoiceDetail>
		            </ShippingDocumentSpecification>
		            <RateRequestTypes>NONE</RateRequestTypes>';           
		            $str_1d.='<PackageCount>1</PackageCount>
		            <RequestedPackageLineItems>
		               <SequenceNumber>'.$sequence_no.'</SequenceNumber>
		               <GroupNumber>1</GroupNumber>
		               <GroupPackageCount>1</GroupPackageCount>
		               <InsuredValue>
		                  <Currency>INR</Currency>
		                  <Amount>0</Amount>
		               </InsuredValue>
		               <Weight>
		                  <Units>KG</Units>
		                  <Value>'.$one_value['RequestedPackageLineItems_Weight'].'</Value>
		               </Weight>
		               <Dimensions>
		                  <Length>'.$domestic_length_ws1.'</Length>
		                  <Width>'.$domestic_width_ws1.'</Width>
		                  <Height>'.$domestic_height_ws1.'</Height>
		                  <Units>CM</Units>
		               </Dimensions>
		               <CustomerReferences>
		                  <CustomerReferenceType>CUSTOMER_REFERENCE</CustomerReferenceType>
		                  <Value>'.$CustomerReferenceType.'</Value>
		               </CustomerReferences>
		            </RequestedPackageLineItems>
		         </RequestedShipment>
		      </ProcessShipmentRequest>
		   </SOAP-ENV:Body>
		</SOAP-ENV:Envelope>';			
//$old_url = "https://ws.".$sh_string.".com/web-services";
		$soaptxt=$str_1d;
		$soap_do = curl_init();
		curl_setopt($soap_do, CURLOPT_URL,             $url);
		curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($soap_do, CURLOPT_POST,           true );            
		curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $soaptxt); 
		curl_setopt($soap_do, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($soaptxt)));
		$data2=curl_exec($soap_do);

		curl_close($soap_do);
		$sd1=str_replace("xmlns:soapenv" ,"xmlns:s2oapenv",trim($data2));
		$sd1=str_replace("xmlns:v15" ,"xmlns:v215",trim($sd1));
		$sd1=str_replace("soapenv:" ,"",trim($sd1));
		$sd1=str_replace("SOAP-ENV:" ,"",trim($sd1));
		$sd1=str_replace("v15:" ,"",trim($sd1));
		$myXMLData=$sd1;
		$xml=simplexml_load_string($myXMLData);
		$xml = json_decode(json_encode($xml),1);
		try{
			//print_r($xml);die;
			if(isset($xml['Body']['Fault'])){
				$erromsg = $xml['Body']['Fault']['detail']['desc'];
	            //throw new \Exception($erromsg);
				throw new \Magento\Framework\Exception\LocalizedException(__($erromsg));
			}
			elseif(isset($xml['Body']['ProcessShipmentReply']) && $xml['Body']['ProcessShipmentReply']['HighestSeverity']!= 'SUCCESS'){ 
			
				$erromsg = $xml['Body']['ProcessShipmentReply']['Notifications']['Message'];
				if(isset($xml['Body']['ProcessShipmentReply']['Notifications'][0]))
					$erromsg = $xml['Body']['ProcessShipmentReply']['Notifications'][0]['Message'];
				throw new \Magento\Framework\Exception\LocalizedException(__($erromsg));
			}
			else{
				return $xml;
			}
		}catch(\Exception $e){
			//echo $e->getMessage();die('________');
			throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
		}
//$track = $xml['Body']['ProcessShipmentReply']['CompletedShipmentDetail']['AssociatedShipments']['TrackingId']['TrackingNumber']; ///TrackingNumber  //uniq number to track
//echo '<pre>';echo $xml->Body->ProcessShipmentReply->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image; ///Label Image //binary data
//echo '<pre>';echo $xml->Body->ProcessShipmentReply->CompletedShipmentDetail->CompletedPackageDetails->Label->ImageType; //Label Image Type ex.: PDF
//echo '<pre>';echo $xml->Body->ProcessShipmentReply->CompletedShipmentDetail->ShipmentDocumentsParts->Image; ///AWB Image //binary data
//echo '<pre>';echo $xml->Body->ProcessShipmentReply->CompletedShipmentDetail->ShipmentDocuments->ImageType; //AWB Image Type ex.: PDF
//print_r(json_decode($track));die('hhh');

	
   }
}
