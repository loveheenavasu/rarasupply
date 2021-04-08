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
namespace Ced\CsCmsPage\Controller\Adminhtml\Vcmspage;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 
class NewAction extends \Magento\Backend\App\Action
{
  
    protected $resultForwardFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    
    public function execute()
    {
    
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }

    
    protected function _isAllowed()
    {
        return true;
    }
	}