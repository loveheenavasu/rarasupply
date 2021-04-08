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
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Advertisement\Controller\Adminhtml\Plan;

 use Magento\Framework\View\Result\PageFactory;
 use Magento\Backend\App\Action\Context;

class Edit extends \Magento\Backend\App\Action
{
     /**
     * @var PageFactory
     */
    protected $resultPageFactory;
 
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
    }

   /**
     * Initiate action
     *
     * @return this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Ced_Advertisement::advertisement')->_addBreadcrumb(__('Advertisements'), __('Add Plans'));
        return $this;
    }

    
    
    
    /**
     * Promo quote edit action
     *
     * @return                                  void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magento\Catalog\Model\Product');
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This Plan no longer exists.'));
                $this->_redirect('advertisement/plan/index');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        
        $this->_coreRegistry->register('plan_data', $model);
        $this->_initAction();
        //    $this->_view->getLayout()->getBlock('vendor_entity_edit')->setData('action', $this->getUrl('csmarketplace/vendor/save'));

        $this->_addBreadcrumb($id ? __('Edit Plan') : __('New Plan'), $id ? __('Edit Plan') : __('New Plan'));

        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getName() : __('New Plan')
        );
        $this->_view->renderLayout();
    }
}
