<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Advertisement\Plugin\View\Element\Html;

/**
 * Links list block
 *
 * @api
 */
class Links
{
    /**
     * Render Block
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $link
     * @return string
     */
    public function afterRenderLink($subject, $result, \Magento\Framework\View\Element\AbstractBlock $link){       
        if($link->getNameInLayout() == 'customer-account-navigation-advertisement-block-link' || 
            $link->getNameInLayout() == 'customer-account-navigation-advertisement-purchase-link' ||
            $link->getNameInLayout() == 'customer-account-navigation-advertisement-purchased-link'){
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->vendorSesion = $this->_objectManager->get('Ced\CsMarketplace\Model\Session');

            $customerId = $this->vendorSesion->getCustomerSession()->getCustomerId();
            $vendor = $this->_objectManager->create('Ced\CsMarketplace\Model\Vendor')->loadByCustomerId($customerId);

            if($vendor && $vendor->getId() && $vendor->getStatus()=='approved'){
                return $result;
            }else{
                return;
            }
        }
        return $result;
    }
}
