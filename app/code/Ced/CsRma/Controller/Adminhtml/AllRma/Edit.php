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

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Ced\CsRma\Model\RequestFactory;
use Magento\Framework\Registry;
 
class Edit extends \Magento\Backend\App\Action
{
    /**
    * @var Ced\CsRma\Model\RequestFactory
    */
    protected $rmaRequestFactory;

    /**
     * @param Action\Context $context
     * @param Registry $registry
     * @param  RequestFactory $rmaRequestFactory
     */
    public function __construct(
        Action\Context $context,
        Registry $registry,
        RequestFactory $rmaRequestFactory
    ) {

        $this->rmaRequestFactory = $rmaRequestFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Ced_CsRma::Ced_Rma::category');

        $id = $this->getRequest()->getParam('id'); 
        $model = $this->rmaRequestFactory->create()->load($id);
        $this->_coreRegistry->register('ced_csrma_request', $model);   
        
        $resultPage->getConfig()->getTitle()->prepend(__('Return Request'));
        $resultPage->getConfig()->getTitle()->prepend(__('#'.$model->getRmaId()));
        //$resultPage->addContent($resultPage->getLayout()->createBlock('Ced\CsRma\Block\Adminhtml\AllRma\Edit'));
        return $resultPage;
    }
}
