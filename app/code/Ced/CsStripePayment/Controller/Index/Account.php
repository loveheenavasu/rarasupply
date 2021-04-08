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
namespace Ced\CsStripePayment\Controller\Index;


use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\Controller\ResultFactory;

class Account extends \Ced\CsMarketplace\Controller\Vendor
{

	/**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerModel;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        ResultFactory $result,
        \Magento\Framework\App\Action\Context $context,
        \Ced\CsStripePayment\Helper\Data $stripeHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsMarketplace\Model\VsettingsFactory $vsettingfactory,        
        \Ced\CsStripePayment\Model\ManagedFactory $managedFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \StripeIntegration\Payments\Model\Config $_stripeConfig,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
    ){
        $this->_stripeConfig = $_stripeConfig;
        $this->stripeHelper = $stripeHelper;
        $this->resultRedirect = $result;
    	$this->regionFactory = $regionFactory;
    	$this->managedFactory = $managedFactory;
    	$this->customersession = $customerSession;
    	$this->vendorFactory = $vendorFactory;
    	$this->vsettingfactory = $vsettingfactory;
    	$this->_scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerModel = $customerModel;
        $this->storeManager = $storeManager;
        $this->moduleReader = $moduleReader;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendorFactory
        );
    }

    public function execute()
    {	
        
        $account_type = $this->_scopeConfig->getValue('ced_csmarketplace/csstripe/account_type');
        $vendorId = $this->customersession->getData('vendor_id');
        $customer = $this->_customerModel->create()->load($this->customersession->getCustomerId());
  		

  		if($account_type == 'managed' && $vendorId){

  			$vendor = $this->vendorFactory->create()->load($vendorId);
  			$vendorSetting = $this->vsettingfactory->create()->getCollection()->addFieldToFilter('vendor_id', $vendorId);
  			$vendorData = [];
  			foreach ( $vendorSetting as $key => $value ) { 
                if ($value ['key'] == "payment/vstripe/stripe_email")
                    $vendorData['email'] = $value ['value'];
                
                if ($value ['key'] == "payment/vstripe/account_number")
                    $vendorData['account'] = $value ['value']; 
                
                if ($value ['key'] == "payment/vstripe/routing_number")
                    $vendorData['routing'] = $value ['value'];
                
            }
            
	    	if($vendorData && $vendor->getData('country_id') && isset($vendorData['account']) && $vendorData['account']) { 
	    		//$state = $vendor->getData('region_id') ? $this->regionFactory->create()->load($vendor->getData('region_id'))->getCode() : $vendor->getData('region');
                
	    		$data = [
                    "type" => "custom",
                    "country" => $vendor->getData('country_id'),
                    "email" => $vendorData['email'],
                    "requested_capabilities" => ['transfers', 'card_payments'],
                    'tos_acceptance' => [
                        'date' => time(),
                        'ip' => $_SERVER['REMOTE_ADDR']
                    ],
                    "external_account" => [
                        "object" => "bank_account",
                        "account_number" => $vendorData['account'],
                        "country" => $vendor->getData('country_id'),
                        "currency" => $this->storeManager->getStore()->getCurrentCurrencyCode()
                    ],
                    "settings" => [
                        "payouts" => [
                            //"debit_negative_balances" => true,
                            "schedule" => [
                                "delay_days" => $this->_scopeConfig->getValue('ced_csmarketplace/csstripe/payout_daily'),
                                "interval" => "daily"
                            ]
                        ]
                    ]
                ];
                if($vendor->getData('country_id')=='JP'){
                	//$data['individual']['ssn'] = $vendorData['ssn'] ? substr($vendorData['ssn'], -4) : '0000';
                	$data['external_account']["account_holder_name"] = $vendor->getData('name');
                }
                if(isset($vendorData['routing']) && $vendorData['routing']){
                	$data['external_account']['routing_number'] = $vendorData['routing'];
                }
                $payoutType = $this->_scopeConfig->getValue('ced_csmarketplace/csstripe/payout_type');
                if($payoutType == 'weekly'){
                    unset($data['settings']['payouts']['schedule']['delay_days']);
                    $payoutDay = $this->_scopeConfig->getValue('ced_csmarketplace/csstripe/payout_weekly');
                    $data['settings']['payouts']['schedule']['interval'] = $payoutType; 
                    $data['settings']['payouts']['schedule']['weekly_anchor'] = $payoutDay;
                }
                if($payoutType == 'monthly'){
                    unset($data['settings']['payouts']['schedule']['delay_days']);
                    $payoutDay = $this->_scopeConfig->getValue('ced_csmarketplace/csstripe/payout_monthly');
                    $data['settings']['payouts']['schedule']['interval'] = $payoutType; 
                    $data['settings']['payouts']['schedule']['monthly_anchor'] = $payoutDay;
                }
                
                //echo '<pre>';
                //print_r($data);die('=-=-=-');
                try{
                	$mode = $this->_stripeConfig->getStripeMode();
                	
                	$skey = $this->_stripeConfig->getSecretKey ($mode);
                	\Stripe\Stripe::setApiKey($skey);
                    $account = \Stripe\Account::create ($data);
                    if($account->id){
                    	$custStripeData = $this->managedFactory->create();
                    	$custStripeData->setData( 'vendor_id', $vendorId )
                                        ->setData( 'account_id', $account->id )
                                        ->setData( 'email_id', $vendorData['email'])
                                        ->save ();
                        $data = [
                                'account' => $account->id,
                                'failure_url' => $this->storeManager->getStore()->getUrl('csstripe/index/failure',['vid' => $vendorId]),
                                'success_url' => $this->storeManager->getStore()->getUrl('csstripe/index/success',['vid' => $vendorId]),
                                'type' => 'custom_account_verification',
                                'collect' => 'eventually_due'
                            ];

                        $url = "https://api.stripe.com/v1/account_links";

                        $result = $this->stripeHelper->linkaccount($url,$data,$skey);
                        
                        if(isset($result['object']) && $result['object']=='account_link' && isset($result['url'])){
                            
                            $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
                            return $resultRedirect->setUrl($result['url']);
                        }
                        if(isset($result['error']) && isset($result['error']['message'])){
	                        $this->messageManager->addErrorMessage(__($result['error']['message']));  
	                    
	                    }
                    }                    

	    		}catch(\Exception $e){ //echo $e->getMessage();die('in catch');
	    			$this->messageManager->addErrorMessage(__($e->getMessage()));	
	    		}
	    	}else{
	    		$this->messageManager->addErrorMessage(__('Vendor Profile Country & Stripe Email, Account Number can not be empty.'));
	    	}
   		}
   		$this->_redirect('csmarketplace/vsettings/index');
	    return;
   		
    }
}