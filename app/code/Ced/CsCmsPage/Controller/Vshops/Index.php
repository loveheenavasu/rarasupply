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
  * @category    Ced
  * @package     Ced_CsCmsPage
  * @author   CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsMarketplace\Controller\Vshops;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;
	
	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry = null;
	
	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		\Magento\Framework\Registry $registry
	) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
		$this->_coreRegistry = $registry;
	}
	/**
	 * Index action
	 *
	 * @return void
	 */
	public function execute()
	{
		$data = $this->getRequest()->getParams();
	
		//print_r($data);die;
		
		if(count($data)==2){
			$this->_coreRegistry->register('product_list_mode',$data['product_list_limit']);
		}
		else
		{
			if(!empty($data))
			{
				
				//$this->_coreRegistry->register('p',$data['p']);
				$this->_coreRegistry->register('vendor_name',$data['char']);
				$this->_coreRegistry->register('country',$data['country_id']);
				$this->_coreRegistry->register('zip_code',$data['estimate_postcode']);
		
			}
		}
		$resultPage = $this->resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->set(__('CsMarketplace'));
		return $resultPage;
	}
}
?>