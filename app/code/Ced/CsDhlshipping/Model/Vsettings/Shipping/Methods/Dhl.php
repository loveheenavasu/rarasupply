<?php 

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
  * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Ced
 * @package     Ced_CsDhlshipping
 * @author 		CedCommerce Core Team <coreteam@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Ced\CsDhlshipping\Model\Vsettings\Shipping\Methods;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\UrlInterface;

class Dhl extends \Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel
{
    protected $_code = 'dhl';
	protected $_fields = array();
	protected $_codeSeparator = '-';
	protected $_scopeConfig;
    protected $_countryFactory;
    protected $_objectManager;
    protected $_storeManager;
    protected $urlBuilder;
	 
	/**
	 * Retreive input fields
	 *
	 * @return array

	 */
	    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Directory\Model\Config\Source\CountryFactory $countryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        UrlInterface $urlBuilder,
        array $data = []
    ) {  
        $this->_storeManager = $storeManager;
        $this->_countryFactory = $countryFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->urlBuilder = $urlBuilder;
    
    }  
	    public function getFields() {
	    	

	    	
		    $vendorId=$this->_objectManager->get('Magento\Customer\Model\Session')->getVendorId();
			$key = 'shipping/'.$this->_code.'/content_type';
			$vsetting_model = $this->_objectManager->get('Ced\CsMarketplace\Model\Vsettings');
			$contenttype = $vsetting_model->loadByField(array('key','vendor_id'),array($key,$vendorId))
							->getValue();
             
			if($contenttype=="")
			{  
				$method =$this->_objectManager->get('Magento\Dhl\Model\Source\Method\Doc')->toOptionArray(); 
			}
			if($contenttype=="0")
			{
				
				$method = $this->_objectManager->get('Magento\Dhl\Model\Source\Method\Doc')->toOptionArray();
			}
			if($contenttype=="1")
			{   
				$method = $this->_objectManager->get('Magento\Dhl\Model\Source\Method\Nondoc')->toOptionArray();
			}
	     $docmethod = json_encode($this->_objectManager->get('Magento\Dhl\Model\Source\Method\Doc')->toOptionArray());
	     $nondocmethod = json_encode($this->_objectManager->get('Magento\Dhl\Model\Source\Method\Nondoc')->toOptionArray());
          

	     $fields['active'] = array('type'=>'select','required'=>true,'values'=>array(array('label'=>__('Yes'),'value'=>1),array('label'=>__('No'),'value'=>0)));
		 $fields['content_type'] = array('type'=>'select', 'onchange'=>'methodupdate()','required'=>true,'values'=>array(array('label'=>__('Non Document'),'value'=>1),array('label'=>__('Document'),'value'=>0)));
		 $fields['allowed_methods']=array('type'=>'multiselect','required'=>true,'values'=>$method);
		 $fields['allowed_methods']['after_element_html']='<script>
				function methodupdate()
				{
				  	var val = document.getElementById("dhl-content_type").value;
					var cats = document.getElementById("dhl-allowed_methods");
					
				    if(val==1) {
					    var valu = '.$nondocmethod.';
					 }else{
				    	var valu = '.$docmethod.';
	                }	
				    cats.innerHTML= "";
				   
	                 for(var i=0;i<valu.length;i++)
	                  {	                     		 
	                      var newDiv = document.createElement("option"); 
				    	  newDiv.value = valu[i].value;
				    	  newDiv.text = valu[i].label;
	                      cats.appendChild(newDiv);	   
				       } 			
	             }
				</script>';
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
			case 'label' : return __('DHL');break;
			case 'content_type': return __('Content Type');break;
			case 'allowed_methods': return __('Allowed Methods');break;
			default : return parent::getLabel($key); break;
		}
	}
}
