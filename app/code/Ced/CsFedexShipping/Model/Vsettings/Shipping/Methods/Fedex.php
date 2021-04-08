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

namespace Ced\CsFedexShipping\Model\Vsettings\Shipping\Methods;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Ced\DomesticAustralianShipping\Helper\Config;

class Fedex extends \Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel
{
    protected $_code = 'fedex';
	protected $_fields = array();
	protected $_codeSeparator = '-';
	 protected $_scopeConfig;
	 protected $_countryFactory;
	 protected $_objectManager;
	/**
	 * Retreive input fields
	 *
	 * @return array
	 */
	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Psr\Log\LoggerInterface $logger,
			\Magento\Directory\Model\Config\Source\CountryFactory $countryFactory,
			\Magento\Framework\ObjectManagerInterface $objectManager,
			array $data = []
	) {
		$this->_countryFactory = $countryFactory;
		$this->_scopeConfig = $scopeConfig;
		$this->_objectManager = $objectManager;
	
	}
	public function getFields() {
		$fields['active'] = array('type'=>'select',
								'required'=>true,
								'values'=>array(
									array('label'=>__('Yes'),'value'=>1),
									array('label'=>__('No'),'value'=>0)
								)
							);
		//$fields['carrier_title'] = array('type'=>'text');
		$fields['allowed_methods']=array('type'=>'multiselect','required'=>true,'values'=>$this->_objectManager->create('Magento\Fedex\Model\Source\Method')->toOptionArray());
		return $fields;
	}
	
	/**
	 * Retreive labels
	 *
	 * @param string $key
	 * @return string
	 */
	public function getLabel($key) {
		switch($key) {
			case 'label' : return __('FEDEX');break;
			
			case 'allowed_methods': return __('Allowed Methods');break;

			default : return parent::getLabel($key); break;
		}
	}
	
	public function validateSpecificMethod($methodData){
		if(count($methodData)>0){
			if(!isset($methodData['allowed_methods'])){
				return false;
			}
			if(isset($methodData['allowed_methods'])){
				if(!strlen($methodData['allowed_methods'])>0){
					return false;
				}
			}
			return true;
		}
		else
			return false;
	}
	
}
