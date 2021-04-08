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
  * @package     Ced_Rewardsystem
  * @author   	 CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */ 
namespace Ced\Rewardsystem\Controller\Adminhtml\Reward;
 
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 
class Editrule extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
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
        
        $this->resultPageFactory = $resultPageFactory;
         $this->_coreRegistry = $registry;
         parent::__construct($context);
    }
 
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $id=$this->getRequest()->getParams();
        $this->_coreRegistry->register('rewardrule_form_data', $id['id']);
         //$data = $this->_coreRegistry->registry('rewardrule_form_data');
     	//Call page factory to render layout and page content
		$resultPage = $this->resultPageFactory->create();

		//Set the menu which will be active for this page
		$resultPage->setActiveMenu('Ced_Rewardsystem::reward_manage');
		
		//Set the header title of grid
		$resultPage->getConfig()->getTitle()->prepend(__('Reward System'));

		//Add bread crumb
		$resultPage->addBreadcrumb(__('Ced'), __('Ced'));
		$resultPage->addBreadcrumb(__('Hello World'), __('Reward System'));

		return $resultPage;
	}

	/*
	 * Check permission via ACL resource
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Ced_Rewardsystem::reward_manage');
	}
}