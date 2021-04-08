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

namespace Ced\CsFedexShipping\Model\Carrier;

use Magento\Framework\Module\Dir;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Xml\Security;

class Fedex extends \Magento\Fedex\Model\Carrier
{
	
	/**
	 * Code of the carrier
	 *
	 * @var string
	 */
	const CODE = 'fedex';
	
	/**
	 * Purpose of rate request
	 *
	 * @var string
	 */
	const RATE_REQUEST_GENERAL = 'general';
	
	/**
	 * Purpose of rate request
	 *
	 * @var string
	 */
	const RATE_REQUEST_SMARTPOST = 'SMART_POST';
	
	/**
	 * Code of the carrier
	 *
	 * @var string
	 */
	protected $_code = self::CODE;
	
	/**
	 * Types of rates, order is important
	 *
	 * @var array
	 */
	protected $_ratesOrder = [
	'RATED_ACCOUNT_PACKAGE',
	'PAYOR_ACCOUNT_PACKAGE',
	'RATED_ACCOUNT_SHIPMENT',
	'PAYOR_ACCOUNT_SHIPMENT',
	'RATED_LIST_PACKAGE',
	'PAYOR_LIST_PACKAGE',
	'RATED_LIST_SHIPMENT',
	'PAYOR_LIST_SHIPMENT',
	];
	
	/**
	 * Rate request data
	 *
	 * @var RateRequest|null
	*/
	protected $_request = null;
	
	/**
	 * Rate result data
	 *
	 * @var Result|null
	 */
	protected $_result = null;
	
	/**
	 * Path to wsdl file of rate service
	 *
	 * @var string
	 */
	protected $_rateServiceWsdl;
	
	/**
	 * Path to wsdl file of ship service
	 *
	 * @var string
	 */
	protected $_shipServiceWsdl = null;
	
	/**
	 * Path to wsdl file of track service
	 *
	 * @var string
	 */
	protected $_trackServiceWsdl = null;
	
	/**
	 * Container types that could be customized for FedEx carrier
	 *
	 * @var string[]
	 */
	protected $_customizableContainerTypes = ['YOUR_PACKAGING'];
	
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	*/
	protected $_storeManager;
	
	/**
	 * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
	 */
	protected $_productCollectionFactory;
	protected $_objectManager;
	protected $_vendorFactory;
	protected $scopeConfig;
	protected $_rateResultFactory;
	protected $_rateMethodFactory;
	/**
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param Security $xmlSecurity
	 * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
	 * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
	 * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
	 * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
	 * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
	 * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
	 * @param \Magento\Directory\Model\RegionFactory $regionFactory
	 * @param \Magento\Directory\Model\CountryFactory $countryFactory
	 * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
	 * @param \Magento\Directory\Helper\Data $directoryData
	 * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Magento\Framework\Module\Dir\Reader $configReader
	 * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
	 * @param array $data
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
	 */
	public function __construct(
	\Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
	\Psr\Log\LoggerInterface $logger,
	Security $xmlSecurity,
	\Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
	\Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
	\Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
	\Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
	\Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
	\Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
	\Magento\Directory\Model\RegionFactory $regionFactory,
	\Magento\Directory\Model\CountryFactory $countryFactory,
	\Magento\Directory\Model\CurrencyFactory $currencyFactory,
	\Magento\Directory\Helper\Data $directoryData,
	\Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magento\Framework\Module\Dir\Reader $configReader,
	\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
	\Magento\Framework\ObjectManagerInterface $objectManager,
	\Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	array $data = []
	) {
		$this->_storeManager = $storeManager;
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->_objectManager = $objectManager;
		$this->_vendorFactory = $vendorFactory;
		$this->scopeConfig = $scopeConfig;
		$this->_rateResultFactory = $rateFactory;
		$this->_rateMethodFactory = $rateMethodFactory;
		parent::__construct(
				$scopeConfig,
				$rateErrorFactory,
				$logger,
				$xmlSecurity,
				$xmlElFactory,
				$rateFactory,
				$rateMethodFactory,
				$trackFactory,
				$trackErrorFactory,
				$trackStatusFactory,
				$regionFactory,
				$countryFactory,
				$currencyFactory,
				$directoryData,
				$stockRegistry,
				$storeManager,
				$configReader,
				$productCollectionFactory,
				$data
		);
		
		$wsdlBasePath = $configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Fedex') . '/wsdl/';
		$this->_shipServiceWsdl = $wsdlBasePath . 'ShipService_v10.wsdl';
		$this->_rateServiceWsdl = $wsdlBasePath . 'RateService_v10.wsdl';
		$this->_trackServiceWsdl = $wsdlBasePath . 'TrackService_v5.wsdl';
	}
	
	public function collectRates(RateRequest $request)
	{
	
		if(!$this->scopeConfig->getValue('carriers/fedex/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
			return;
		
		if(!$this->_objectManager->get('Ced\CsMultiShipping\Helper\Data')->isEnabled())
		{
			return parent::collectRates($request);
		}
		if(!$this->_objectManager->get('Ced\CsFedexShipping\Helper\Data')->isEnabled())
			return parent::collectRates($request);
			//echo $request->getVendorId();die('===');
		if($request->getVendorId()=='admin'){
		    return parent::collectRates($request);
		}
		$this->setRequest($request);
	
		$this->_getQuotes();
	
		$this->_updateFreeMethodQuote($request);
	
		return $this->getResult();
	}
	/*vendorwise  _getQuotes()*/
	protected function _getQuotes()
	{
		if(!$this->_objectManager->get('Ced\CsMultiShipping\Helper\Data')->isEnabled())
		{
			return parent::_getQuotes();
		}
		if(!$this->_objectManager->get('Ced\CsFedexShipping\Helper\Data')->isEnabled())
			return parent::_getQuotes();
		$this->_result = $this->_rateResultFactory->create();
		$vendorId = $this->_request->getVendorId();
	
		if($vendorId=='admin')
			return parent::_getQuotes();
		$items=$this->_request->getVendorItems();
		$vendor=array();
		$fedexSpecificConfig=array();
		if($vendorId!="admin")
			$fedexSpecificConfig = $this->_request->getVendorShippingSpecifics();
	
	
		if($vendorId!="admin")
		{
			$vendor =$this->_vendorFactory->create()->load($vendorId);
	
		}
		else
		{
			$vendor=$this->_vendorFactory->create();
		}
		if($vendorId!="admin")
		{
			$allowedMethods = explode(',', $fedexSpecificConfig['allowed_methods']);
		}
		else
		{
			$allowedMethods = explode(',', $this->getConfigData('allowed_methods'));
		}
	
		if (in_array(self::RATE_REQUEST_SMARTPOST, $allowedMethods))
		{				
			$response = $this->_doRatesRequest(self::RATE_REQUEST_SMARTPOST);		
			$preparedGeneral = $this->_prepareRateResponseNew($response,$vendor,$fedexSpecificConfig);
			//$this->_result->append($preparedSmartpost);
		}else{	
			$response = $this->_doRatesRequest(self::RATE_REQUEST_GENERAL);
			//print_r($response);die('+++++++');			
			$preparedGeneral = $this->_prepareRateResponseNew($response,$vendor,$fedexSpecificConfig);
			//print_r($preparedGeneral);die('===');
		}
		if ($this->_result->getError() && $preparedGeneral->getError())
		{
		    
			return $this->_result->getError();
		}
		$this->_result->append($preparedGeneral);
	
	
		return $this->_result;
	}
	/**
	 *prepare request which is to be sent, returns a raterequest
	 *
	 * @param string $purpose
	 * @return request formed
	 */
	protected function _formRateRequest($purpose)
	{ 
		if(!$this->_objectManager->get('Ced\CsMultiShipping\Helper\Data')->isEnabled())
		{ 
			return parent::_formRateRequest($purpose);
		}
		if(!$this->_objectManager->get('Ced\CsFedexShipping\Helper\Data')->isEnabled())
			return parent::_formRateRequest($purpose);
		$vendorId = $this->_request->getVendorId();
		if($vendorId=='admin')
			return parent::_formRateRequest($purpose);
		$items=$this->_request->getVendorItems();
		$vendor=array();
		$fedexSpecificConfig=array();
		if($vendorId!="admin")
			$fedexSpecificConfig = $this->_request->getVendorShippingSpecifics();
		if($vendorId!="admin")
		{
			$vendor =$this->_vendorFactory->create()->load($vendorId);
		}
		else
		{
			$vendor =$this->_vendorFactory->create();
		}
			
		$r = $this->_rawRequest;
	    //echo $r->getKey();die('===');
		/*$ratesRequest = [
            'WebAuthenticationDetail' => [
                'UserCredential' => ['Key' => $r->getKey(), 'Password' => $r->getPassword()],
            ],
            'ClientDetail' => ['AccountNumber' => $r->getAccount(), 'MeterNumber' => $r->getMeterNumber()],
            'Version' => $this->getVersionInfo(),
            'RequestedShipment' => [
                'DropoffType' => $r->getDropoffType(),
                'ShipTimestamp' => date('c'),
                'PackagingType' => $r->getPackaging(),
                'TotalInsuredValue' => ['Amount' => $r->getValue(), 'Currency' => $this->getCurrencyCode()],
                'Shipper' => [
                    'Address' => ['PostalCode' => $r->getOrigPostal(), 'CountryCode' => $r->getOrigCountry()],
                ],
                'Recipient' => [
                    'Address' => [
                        'PostalCode' => $r->getDestPostal(),
                        'CountryCode' => $r->getDestCountry(),
                        'Residential' => (bool)$this->getConfigData('residence_delivery'),
                    ],
                ],
                'ShippingChargesPayment' => [
                    'PaymentType' => 'SENDER',
                    'Payor' => ['AccountNumber' => $r->getAccount(), 'CountryCode' => $r->getOrigCountry()],
                ],
                'CustomsClearanceDetail' => [
                    'CustomsValue' => ['Amount' => $r->getValue(), 'Currency' => $this->getCurrencyCode()],
                    
                ],
                'RateRequestTypes' => 'LIST',
                'PackageCount' => '1',
                'PackageDetail' => 'INDIVIDUAL_PACKAGES',
                'RequestedPackageLineItems' => [
                    '0' => [
                        'Weight' => [
                            'Value' => (double)$r->getWeight(),
                            'Units' => $this->getConfigData('unit_of_measure'),
                        ],
                        'GroupPackageCount' => 1,
                    ],
                ],
            ],
        ];*/
        
        $ratesRequest = [
            'WebAuthenticationDetail' => [
                'UserCredential' => ['Key' => $r->getKey(), 'Password' => $r->getPassword()],
            ],
            'ClientDetail' => ['AccountNumber' => $r->getAccount(), 'MeterNumber' => $r->getMeterNumber()],
            'Version' => $this->getVersionInfo(),
            'RequestedShipment' => [
                'DropoffType' => $r->getDropoffType(),
                'ShipTimestamp' => date('c'),
                'PackagingType' => $r->getPackaging(),
                'TotalInsuredValue' => ['Amount' => $r->getValue(), 'Currency' => $this->getCurrencyCode()],
                'Shipper' => [
                    'Address' => ['PostalCode' => $r->getOrigPostal(), 'CountryCode' => $r->getOrigCountry()],
                ],
                'Recipient' => [
                    'Address' => [
                        'PostalCode' => $r->getDestPostal(),
                        'CountryCode' => $r->getDestCountry(),
                        'Residential' => (bool)$this->getConfigData('residence_delivery'),
                    ],
                ],
                'ShippingChargesPayment' => [
                    'PaymentType' => 'SENDER',
                    'Payor' => ['AccountNumber' => $r->getAccount(), 'CountryCode' => $r->getOrigCountry()],
                ],
                'CustomsClearanceDetail' => [
                    'CustomsValue' => ['Amount' => $r->getValue(), 'Currency' => $this->getCurrencyCode()],
                    'CommercialInvoice' => ['Purpose' => "SOLD"],
                ],
                'RateRequestTypes' => 'LIST',
                'PackageCount' => '1',
                'PackageDetail' => 'INDIVIDUAL_PACKAGES',
                'RequestedPackageLineItems' => [
                    '0' => [
                        'Weight' => [
                            'Value' => (double)$r->getWeight(),
                            'Units' => $this->getConfigData('unit_of_measure'),
                        ],
                        'GroupPackageCount' => 1,
                    ],
                ],
            ],
        ];
        /*if ($purpose == self::RATE_REQUEST_GENERAL) {
            $ratesRequest['RequestedShipment']['RequestedPackageLineItems'][0]['InsuredValue'] = [
                'Amount' => $r->getValue(),
                'Currency' => $this->getCurrencyCode(),
            ];
        } else {
            if ($purpose == self::RATE_REQUEST_SMARTPOST) {
                $ratesRequest['RequestedShipment']['ServiceType'] = self::RATE_REQUEST_SMARTPOST;
                $ratesRequest['RequestedShipment']['SmartPostDetail'] = [
                    'Indicia' => (double)$r->getWeight() >= 1 ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                    'HubId' => $this->getConfigData('smartpost_hubid'),
                ];
            }
        }*/
        if ($r->getDestCity()) {
            $ratesRequest['RequestedShipment']['Recipient']['Address']['City'] = $r->getDestCity();
        }

        if ($purpose == self::RATE_REQUEST_GENERAL) { 
            $ratesRequest['RequestedShipment']['RequestedPackageLineItems'][0]['InsuredValue'] = [
                'Amount' => $r->getValue(),
                'Currency' => $this->getCurrencyCode(),
            ];
            return $ratesRequest;
        } else {
            if ($purpose == self::RATE_REQUEST_SMARTPOST) {  
                $ratesRequest['RequestedShipment']['ServiceType'] = self::RATE_REQUEST_SMARTPOST;
                $ratesRequest['RequestedShipment']['SmartPostDetail'] = [
                    'Indicia' => (double)$r->getWeight() >= 1 ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                    'HubId' => $this->getConfigData('smartpost_hubid'),
                ];
                return $ratesRequest;
            }
        }
        //print_r($ratesRequests);die;
        //return $ratesRequest;
	}
	
	/**
	 * Makes remote request to the carrier and returns a response
	 *
	 * @param string $purpose
	 * @return mixed
	 */
	protected function _doRatesRequest($purpose)
	{	if(!$this->_objectManager->get('Ced\CsMultiShipping\Helper\Data')->isEnabled())
		{	
		    return parent::_doRatesRequest($purpose);
		}
    	if(!$this->_objectManager->get('Ced\CsFedexShipping\Helper\Data')->isEnabled())
    		return parent::_doRatesRequest($purpose);
		if($this->_request->getVendorId()=='admin')
		    return parent::_doRatesRequest($purpose);
	    $ratesRequest = $this->_formRateRequest($purpose);	
		$requestString = serialize($ratesRequest);
		$response = $this->_getCachedQuotes($requestString);
		$debugData = array('request' => $ratesRequest);
		if ($response === null)
		{
			try
			{
				$client = $this->_createRateSoapClient();
				$response = $client->getRates($ratesRequest);
				$this->_setCachedQuotes($requestString, serialize($response));
				$debugData['result'] = $response;
		            
			}catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                $this->_logger->critical($e);
	        }
		}
		else
		{
			$response = unserialize($response);
			$debugData['result'] = $response;
		}
		$this->_debug($debugData);
		
		return $response;
	}
	
	/**
	 * Prepare shipping rate result based on response
	 *
	 * @param mixed $response
	 * @return Mage_Shipping_Model_Rate_Result
	 */
	protected function _prepareRateResponseNew($response,\Ced\CsMarketplace\Model\Vendor $vendor,$fedexSpecificConfig)
	{
		$costArr = array();
		$priceArr = array();
		$errorTitle = 'Unable to retrieve tracking';
	    //print_r($vendor->getId());die;
		if (is_object($response))
		{
			if ($response->HighestSeverity == 'FAILURE' || $response->HighestSeverity == 'ERROR')
			{
				if (is_array($response->Notifications))
				{
					$notification = array_pop($response->Notifications);
					$errorTitle = (string)$notification->Message;
				}
				else
				{
					$errorTitle = (string)$response->Notifications->Message;
				}
			}
			elseif (isset($response->RateReplyDetails))
			{
	
	
	
				$allowedMethods=array();
				if($vendor->getId())
				{
					if(isset($fedexSpecificConfig['allowed_methods']))
						$allowedMethods = explode(',',$fedexSpecificConfig['allowed_methods']);
					else
					{
						$arr = $this->getCode('method');
						$allowedMethods = array_keys($arr);
					}
				}
				else
				{
					$allowedMethods = explode(",", $this->getConfigData('allowed_methods'));
				}
				
				if (is_array($response->RateReplyDetails))
				{
					foreach ($response->RateReplyDetails as $rate)
					{
						$serviceName = (string)$rate->ServiceType;
						if (in_array($serviceName, $allowedMethods))
						{
							$amount = $this->_getRateAmountOriginBased($rate);
							$costArr[$serviceName]  = $amount;
							$priceArr[$serviceName] = $this->getMethodPrice($amount, $serviceName);
						}
					}
					asort($priceArr);
				}
				else
				{
					$rate = $response->RateReplyDetails;
					$serviceName = (string)$rate->ServiceType;
					if (in_array($serviceName, $allowedMethods))
					{
						$amount = $this->_getRateAmountOriginBased($rate);
						$costArr[$serviceName]  = $amount;
						$priceArr[$serviceName] = $this->getMethodPrice($amount, $serviceName);
					}
				}
			}
		}
		//print_r($priceArr);die('---');
		$result = $this->_rateResultFactory->create();
		if (empty($priceArr))
		{ 
			$error = $this->_rateErrorFactory->create();
			$error->setCarrier($this->_code);
			$error->setCarrierTitle($this->getConfigData('title'));
			$error->setErrorMessage($errorTitle);
			 
			$result->append($error);
		}
		else
		{
			//print_r($priceArr);die('jnsd');
			foreach ($priceArr as $method=>$price)
			{ 
				$rate = $this->_rateMethodFactory->create();
				if($vendor->getId())
				{
				    $custom_method=$method.\Ced\CsMultiShipping\Model\Shipping::SEPARATOR.$vendor->getId();
					$rate->setVendorId($vendor->getId());
				}
				else
					$custom_method=$method;
				$rate->setCarrier($this->_code);
				$rate->setCarrierTitle($this->getConfigData('title'));
				$rate->setMethod($custom_method);
				$method_arr = $this->getCode('method', $method);
				//echo $method.'<br>';
				//print_r($method_arr);die('===');
				$rate->setMethodTitle($method_arr);
				$rate->setCost($price);
				$rate->setPrice($price);
				$result->append($rate);
				//print_r($costArr[$method]);
				//echo '======='.$price;
				//die('===');
			}

		}
		//print_r($result);die;
		return $result;
	}

	public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        $this->_getXmlTrackingInfo($trackings);
        return $this->_result;
    }

    public function _getXmlTrackingInfo($trackings){
        $result = $this->_trackFactory->create();
        $title = $this->scopeConfig->getValue('carriers/fedex/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $track_url = 'http://www.fedex.com/Tracking?action=track&tracknumbers';//$this->_scopeConfig->getValue('carriers/smsashipping/track_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        foreach ($trackings as $tracking) {
            $status = $this->_trackStatusFactory->create();
            $status->setCarrier($this->_code);
            $status->setCarrierTitle($title);
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl("{$track_url}={$tracking}");
            $result->append($status);
        }
        $this->_result = $result;
        return $result;
    }
	
}
