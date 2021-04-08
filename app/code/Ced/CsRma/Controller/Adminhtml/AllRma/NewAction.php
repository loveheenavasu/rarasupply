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
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */ 
namespace Ced\CsRma\Controller\Adminhtml\AllRma;

class NewAction extends \Magento\Backend\App\Action

{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */

    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
    	  \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);

    }

    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */

    public function execute()
    {
    	$resultPage = $this->resultPageFactory->create();
    	$resultPage->getConfig()->getTitle()->prepend(__('New RMA Request')); 
      
    	$resultPage->addContent($resultPage->getLayout()->createBlock('Ced\CsRma\Block\Adminhtml\AllRma\Order\Grid'));

     	$resultPage->addContent($resultPage->getLayout()->createBlock('Ced\CsRma\Block\Adminhtml\AllRma\Add')); 
    	
        return $resultPage; 
    }
}