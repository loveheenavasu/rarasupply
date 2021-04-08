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

class Linkaccount extends \Ced\CsMarketplace\Controller\Vendor
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
        \Ced\CsStripePayment\Helper\Data $stripeHelper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsMarketplace\Model\VsettingsFactory $vsettingfactory,        
        \Ced\CsStripePayment\Model\ManagedFactory $managedFactory,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \StripeIntegration\Payments\Model\Config $_stripeConfig,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
    ){
        $this->_stripeConfig = $_stripeConfig;
        $this->stripeHelper = $stripeHelper;
        $this->resultRedirect = $result;
    	$this->managedFactory = $managedFactory;
    	$this->customersession = $customerSession;
    	$this->vendorFactory = $vendorFactory;
    	$this->vsettingfactory = $vsettingfactory;
    	$this->_scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerModel = $customerModel;
        $this->storeManager = $storeManager;
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
  		if($vendorId)
        {
            try
            {
            	$mode = $this->_stripeConfig->getStripeMode();   
                $skey = $this->_stripeConfig->getSecretKey ($mode);
                $vendorStripeData =  $this->managedFactory->create()->load($vendorId,'vendor_id');
                if(count($vendorStripeData->getData()) && $vendorStripeData->getAccountId())
                {
                    $data = [
                        'account' => $vendorStripeData->getAccountId(),
                        'failure_url' => $this->storeManager->getStore()->getUrl('csstripe/index/failure',['vid' => $vendorId]),
                        'success_url' => $this->storeManager->getStore()->getUrl('csstripe/index/success',['vid' => $vendorId]),
                        'type' => 'custom_account_verification',
                        'collect' => 'eventually_due',
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

    		}catch(\Exception $e){
    			$this->messageManager->addErrorMessage(__($e->getMessage()));	
    		}
	    }else{ 
	    	$this->messageManager->addErrorMessage(__('Vendor Profile Country & Stripe Email can not be empty. '));	
   		}
   		$this->_redirect('csmarketplace/vsettings/index');
	    return;
   		
    }

}
