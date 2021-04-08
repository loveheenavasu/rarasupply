<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Ced\CsDhlshipping\Model;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Module\Dir;
use Magento\Sales\Model\Order\Shipment;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Xml\Security;

/**
 * DHL International (API v1.4)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends \Magento\Dhl\Model\Carrier
{
    /**#@+
     * Carrier Product indicator
     */
    const DHL_CONTENT_TYPE_DOC = 'D';
    const DHL_CONTENT_TYPE_NON_DOC = 'N';
    /**#@-*/

    /**#@+
     * Minimum allowed values for shipping package dimensions
     */
    const DIMENSION_MIN_CM = 3;
    const DIMENSION_MIN_IN = 1;
    /**#@-*/

    /**
     * Config path to UE country list
     */
    const XML_PATH_EU_COUNTRIES_LIST = 'general/country/eu_countries';

    /**
     * Container types that could be customized
     *
     * @var string[]
     */
    protected $_customizableContainerTypes = [self::DHL_CONTENT_TYPE_NON_DOC];

    /**
     * Code of the carrier
     */
    const CODE = 'dhl';

    /**
     * Rate request data
     *
     * @var RateRequest|null
     */
    protected $_request;

    /**
     * Rate result data
     *
     * @var Result|null
     */
    protected $_result;

    /**
     * Countries parameters data
     *
     * @var \Magento\Shipping\Model\Simplexml\Element|null
     */
    protected $_countryParams;

    /**
     * Errors placeholder
     *
     * @var string[]
     */
    protected $_errors = [];

    /**
     * Dhl rates result
     *
     * @var array
     */
    protected $_rates = [];

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Free Method config path
     *
     * @var string
     */
    protected $_freeMethod = 'free_method_nondoc';

    /**
     * Max weight without fee
     *
     * @var int
     */
    protected $_maxWeight = 70;

    /**
     * Flag if response is for shipping label creating
     *
     * @var bool
     */
    protected $_isShippingLabelFlag = false;

    /**
     * Request variables array
     *
     * @var array
     */
    protected $_requestVariables = [
        'id' => ['code' => 'dhl_id', 'setCode' => 'id'],
        'password' => ['code' => 'dhl_password', 'setCode' => 'password'],
        'account' => ['code' => 'dhl_account', 'setCode' => 'account_nbr'],
        'shipping_key' => ['code' => 'dhl_shipping_key', 'setCode' => 'shipping_key'],
        'shipping_intlkey' => ['code' => 'dhl_shipping_intl_key', 'setCode' => 'shipping_intl_key'],
        'shipment_type' => ['code' => 'dhl_shipment_type', 'setCode' => 'shipment_type'],
        'dutiable' => ['code' => 'dhl_dutiable', 'setCode' => 'dutiable'],
        'dutypaymenttype' => ['code' => 'dhl_duty_payment_type', 'setCode' => 'duty_payment_type'],
        'contentdesc' => ['code' => 'dhl_content_desc', 'setCode' => 'content_desc'],
    ];

    /**
     * Flag that shows if shipping is domestic
     *
     * @var bool
     */
    protected $_isDomestic = false;

    /**
     * Core string
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * Carrier helper
     *
     * @var \Magento\Shipping\Helper\Carrier
     */
    protected $_carrierHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_configReader;

    /**
     * @var \Magento\Framework\Math\Division
     */
    protected $mathDivision;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * @inheritdoc
     */
    protected $_debugReplacePrivateDataKeys = [
        'SiteID', 'Password'
    ];

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
     * @param \Magento\Shipping\Helper\Carrier $carrierHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Framework\Module\Dir\Reader $configReader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Math\Division $mathDivision
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
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
        \Magento\Shipping\Helper\Carrier $carrierHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Framework\Module\Dir\Reader $configReader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Math\Division $mathDivision,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        array $data = []
    ) {
         
        $this->readFactory = $readFactory;
        $this->_carrierHelper = $carrierHelper;
        $this->_coreDate = $coreDate;
        $this->_storeManager = $storeManager;
        $this->_configReader = $configReader;
        $this->_objectManager = $objectManager;
        $this->string = $string;
        $this->mathDivision = $mathDivision;
        $this->_dateTime = $dateTime;
        $this->_httpClientFactory = $httpClientFactory;
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
            $carrierHelper,
            $coreDate,
            $configReader,
            $storeManager,
            $string,
            $mathDivision,
            $readFactory,
            $dateTime,
            $httpClientFactory,
            $data
        );

        if ($this->getConfigData('content_type') == self::DHL_CONTENT_TYPE_DOC) {
            $this->_freeMethod = 'free_method_doc';
        }
  
    }

   

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return bool|Result|Error
     */
    

  

    

    /**
     * Parse response from DHL web service
     *
     * @param string $response
     * @return bool|\Magento\Framework\DataObject|Result|Error
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _parseResponse($response)
    {   
        if($this->_request->getVendorId()=='admin'){

            return parent::_parseResponse($response);
        }
        $allallowedmethod =[];
        $allowedmethods = $this->_request->getVendorShippingSpecifics();
        
        //$allowedmethods = explode(',',$allowedmethods['allowed_methods']);
      if(isset($allowedmethods['allowed_methods'])){

        $allallowedmethod = explode(',',$allowedmethods['allowed_methods']);
      }

        

       try
        {
           $responseError = __('The response is in wrong format.');

        if (strlen(trim($response)) > 0) {
            if (strpos(trim($response), '<?xml') === 0) {
                $xml = simplexml_load_string($response);
                //print_r($xml);die;
                //$this->parseXml($response, 'Magento\Shipping\Model\Simplexml\Element');
                if (is_object($xml)) {
                    if (in_array($xml->getName(), ['ErrorResponse', 'ShipmentValidateErrorResponse'])
                        || isset($xml->GetQuoteResponse->Note->Condition)
                    ) {
                        $code = null;
                        $data = null;
                        if (isset($xml->Response->Status->Condition)) {
                            $nodeCondition = $xml->Response->Status->Condition;
                        } else {
                            $nodeCondition = $xml->GetQuoteResponse->Note->Condition;
                        }

                        if ($this->_isShippingLabelFlag) {
                            foreach ($nodeCondition as $condition) {
                                $code = isset($condition->ConditionCode) ? (string)$condition->ConditionCode : 0;
                                $data = isset($condition->ConditionData) ? (string)$condition->ConditionData : '';
                                if (!empty($code) && !empty($data)) {
                                    break;
                                }
                            }
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('Error #%1 : %2', trim($code), trim($data))
                            );
                        }

                        $code = isset($nodeCondition->ConditionCode) ? (string)$nodeCondition->ConditionCode : 0;
                        $data = isset($nodeCondition->ConditionData) ? (string)$nodeCondition->ConditionData : '';
                        $this->_errors[$code] = __('Error #%1 : %2', trim($code), trim($data));
                    } else {

                        if (isset($xml->GetQuoteResponse->BkgDetails->QtdShp)) {
                            
                            foreach ($xml->GetQuoteResponse->BkgDetails->QtdShp as $quotedShipment) {
                            $this->_addRate($quotedShipment);
                            }
                        } elseif (isset($xml->AirwayBillNumber)) {
                            return $this->_prepareShippingLabelContent($xml);
                        } else {
                            $this->_errors[] = $responseError;
                        }
                    }
                }
            } else {
                $this->_errors[] = $responseError;
            }
        } else {
            $this->_errors[] = $responseError;
        }

    }catch(\Exception $e){echo $e->getMessage();die('oooooo');}

    try{ 

//echo $this->_request->getVendorId();die('dfdfdfd');
        /* @var $result Mage_Shipping_Model_Rate_Result */
        $result = $this->_objectManager->create('Magento\Shipping\Model\Rate\Result');
         
        if ($this->_rates) {
           $custom_method=array();
          
            foreach ($this->_rates as $rate) {
                
                $method = $rate['service'];
                if(in_array($method, $allallowedmethod)) {
                $data = $rate['data'];
                /* @var $rate Mage_Shipping_Model_Rate_Result_Method */
                $rate =$this->_objectManager->create('\Magento\Quote\Model\Quote\Address\RateResult\Method');
                $rate->setVendorId($this->_request->getVendorId()); 
                
                $custom_method=$method.\Ced\CsMultiShipping\Model\Shipping::SEPARATOR.$this->_request->getVendorId();
                
            //   echo $custom_method;die('ddffdfd');
                $rate->setCarrier($this->_code);
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($custom_method);
                $rate->setMethodTitle($data['term']);
                $rate->setCost($data['price_total']);
                $rate->setPrice($data['price_total']);
                $result->append($rate);
            }
          
         }

        } else if (!empty($this->_errors)) {
            if ($this->_isShippingLabelFlag) {
                  throw new \Magento\Framework\Exception\LocalizedException($responseError);
            }
            //return $this->_showError();
        }
        }catch(\Exception $e){echo $e->getMessage();die('-----in parse');}
     
        return $result;

   }



    public function getAllowedMethodsVendor($contentType,$allowedMethods)
    {
        $content = $contentType;

        if ($this->_isDomestic) {
            $allowedMethods = array_merge(
                explode(',', $this->getConfigData('doc_methods')),
                explode(',', $this->getConfigData('nondoc_methods'))
            );
        } else {
            switch ($content) {
                case self::DHL_CONTENT_TYPE_DOC:
                    $allowedMethods = explode(',', $allowedMethods);
                    break;
                case self::DHL_CONTENT_TYPE_NON_DOC:
                    $allowedMethods = explode(',', $allowedMethods);
                    break;
                default:
                    throw new \Magento\Framework\Exception\LocalizedException(__('Wrong Content Type'));
            }
        }
        $methods = [];
        foreach ($allowedMethods as $method) {
            $methods[$method] = $this->getDhlProductTitle($method);
        }

        return $methods;
    }
   
}
