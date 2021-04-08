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
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Advertisement\Controller\Adminhtml\Plan;

class MassDelete extends \Magento\Backend\App\Action
{


    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('id');
        if (!is_array($ids)) {
            $this->messageManager->addErrorMessage(__('Please select plan(s).'));
        } else {
            if (!empty($ids)) {
                try {
                    foreach ($ids as $id) {
                        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id);
                        $product->delete();
                    }
                    $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been deleted.', count($ids)));
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }
        return $this->_redirect('*/plan/index');

    }
}
