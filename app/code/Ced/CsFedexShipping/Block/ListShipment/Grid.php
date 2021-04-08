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
  * @package   Ced_CsImportAwb
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsFedexShipping\Block\ListShipment;
use Magento\Customer\Model\Session;
class Grid extends \Ced\CsOrder\Block\ListShipment\Grid
{
    
 
    
    protected function _prepareMassaction()
    {
      
      $this->setMassactionIdField ( 'entity_id' );
      $this->getMassactionBlock ()->setFormFieldName ( 'shipment_ids' );
        $this->getMassactionBlock()->addItem(
            'shippingLabel',
            [
            'label' => __('Get Fedex Shipping Label'),
            'url' => $this->getUrl('csfedexshipping/index/shippinglabel'),
            //'confirm' => __('Only pending Orders Can be Confirmed?')
            ]
        );
    
        $this->getMassactionBlock()->addItem(
            'menifestLabel',
            [
            'label' => __('Get Fedex Menifest Label'),
            'url' => $this->getUrl('csfedexshipping/index/manifestolabel'),
            //'confirm' => __('Only pending Orders Can be Cancelled?')
            ]
        );

       
      return $this;
    }
   
}
