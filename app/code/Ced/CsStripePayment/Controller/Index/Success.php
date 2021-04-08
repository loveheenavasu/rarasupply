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

class Success extends \Ced\CsMarketplace\Controller\Vendor
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
        \Magento\Framework\Controller\ResultFactory $result,
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
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
    ){
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
        //print_r($this->getRequest()->getParams());
        //print_r($this->getRequest()->getPost());die(get_class($this));
        $account_type = $this->_scopeConfig->getValue('ced_csmarketplace/csstripe/account_type');
        $vendorId = $this->getRequest()->getParam('vid');
  		if($vendorId){
            try{
            	
                $vendorStripeData =  $this->managedFactory->create()->load($vendorId,'vendor_id');
                if(count($vendorStripeData->getData())){
                    $vendorStripeData->setData('status',1)->save();
                    $this->messageManager->addSuccessMessage(__('Stripe Account has been confirmed and activated.'));
                }

    		}catch(\Exception $e){ echo $e->getMessage();die('in catch');
    			$this->messageManager->addErrorMessage(__($e->getMessage()));
    			
    		}
	    }else{
            
	    	$this->messageManager->addErrorMessage(__('Something went wrong Try again later.'));
	    	
   		}
   		$this->_redirect('csmarketplace/vsettings/index');
	    return;
   		
    }
}