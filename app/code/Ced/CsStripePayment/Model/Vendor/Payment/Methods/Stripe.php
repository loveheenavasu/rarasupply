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
 * @category  Ced
 * @package   Ced_CsStripePayment
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsStripePayment\Model\Vendor\Payment\Methods;

use Magento\Directory\Helper\Data as DirectoryHelper;
/**
 * Class Stripe
 * @package Ced\CsStripePayment\Model\Vendor\Payment\Methods
 */
class Stripe extends \Ced\CsMarketplace\Model\Vendor\Payment\Methods\AbstractModel
{
    /**
     * @var string
     */
    protected $_code = 'vstripe';
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    protected $vsettingfactory;

    /**
     * Stripe constructor.
     * @param VsettingsFactory $vsettingfactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param DirectoryHelper|null $directory
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlbuilder,
        \Ced\CsMarketplace\Model\VsettingsFactory $vsettingfactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Ced\CsStripePayment\Model\ManagedFactory $managedFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null)
    {
        $this->urlbuilder = $urlbuilder;
        $this->scopeConfig = $scopeConfig;
        $this->vsettingfactory = $vsettingfactory;
        $this->_customerSession = $customerSession;
        $this->managedFactory = $managedFactory;
        parent::__construct(
            $storeManager,
            $request,
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
    }

    /**
     * Retreive input fields
     *
     * @return array
     */
    public function getFields()
    {
        
        $fields = parent::getFields();
        $redirect_uri= $this->urlbuilder->getUrl('csstripe/index/index');
        $account_type = $this->scopeConfig->getValue('ced_csmarketplace/csstripe/account_type');
        
        if($account_type=='standalone'){

            $clientId= $this->scopeConfig->getValue('ced_csmarketplace/csstripe/client_id');
            $url='https://connect.stripe.com/oauth/authorize?response_type=code&client_id='.$clientId.'&scope=read_write';
            $fields['']=['type'=>'text','class'=>'hide',
                'after_element_html'=>'<a class="btn btn-primary uptransform" href='.$url.'><font color="white">Connect With Stripe</font></a>'
            ];

        }else{ 

            $vsetting = $this->vsettingfactory->create()->getCollection()->addFieldToFilter('vendor_id',$this->_customerSession->getData('vendor_id'))->addFieldToFilter('group','payment')->addFieldToFilter('key','payment/vstripe/active');
                    
            $custStripeData = $this->managedFactory->create()->load($this->_customerSession->getData('vendor_id'),'vendor_id');
            if(!count($custStripeData->getData()) && count($vsetting->getData()) && $account_type=='managed'){
                $fields['']=['type'=>'text',
                    'class'=>'hide',
                    'after_element_html'=>'<a class="btn btn-primary uptransform" 
                    href='.$this->urlbuilder->getUrl('csstripe/index/account').'><font color="white">Connect With Stripe</font></a>'
                ];
            }
            if(count($custStripeData->getData()) && count($vsetting->getData()) && $account_type && true){

                $label = $custStripeData->getStatus() ? 'Update Account' : 'Verify Account';
                $fields['']= ['type'=>'text',
                    'class'=>'hide',
                    'after_element_html'=>'<a class="btn btn-primary uptransform" 
                    href='.$this->urlbuilder->getUrl('csstripe/index/linkaccount').'><font color="white">'.$label.'</font></a>'
                ];
            }

            $fields['stripe_email'] = ['type'=>'text','after_element_html'=>'<a href="https://stripe.com/docs" target="_blank">Start accepting payments via Stripe!</a><script type="text/javascript"> setTimeout(\'if(document.getElementById("'.$this->getCode().$this->getCodeSeparator().'active").value == "1") { document.getElementById("'.$this->getCode().$this->getCodeSeparator().'stripe_email").className = "required-entry validate-email input-text";}\',500);</script>'];

            if (isset($fields['active']) && isset($fields['stripe_email'])) {

                $fields['active']['onchange'] = "if(this.value == '1') { document.getElementById('".$this->getCode().$this->getCodeSeparator()."stripe_email').className = 'required-entry validate-email input-text';} else { document.getElementById('".$this->getCode().$this->getCodeSeparator()."stripe_email').className = 'input-text'; } ";
            }
            $fields['account_number'] = ['type'=>'text',"document.getElementById('".$this->getCode().$this->getCodeSeparator()."account_number').attr('readonly', true)"];
            $fields['routing_number'] = ['type'=>'text','after_element_html'=>'If needed for your country.'];
            
        }

        return $fields;
    }

    /**
     * Retreive labels
     *
     * @param  string $key
     * @return string
     */
    public function getLabel($key)
    {
        switch($key) {
            case 'label' : return __('Stripe Payment');break;
            case 'stripe_email' : return __('Email to Associat with Stripe Connect Account');break;
            case 'account_number'	:return __('Bank Account Number');break;
            case 'routing_number'	:return __('Routing Number');break;
            default : return parent::getLabel($key); break;
        }
    }
}
