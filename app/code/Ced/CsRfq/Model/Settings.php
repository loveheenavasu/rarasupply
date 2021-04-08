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
 
namespace Ced\CsRfq\Model;

class Settings extends \Ced\CsMarketplace\Model\Vendor\Payment\Methods\AbstractModel
{
    protected $_code = 'rfq';
    CONST RFQ = "rfq";
    /**
     * Retrieve input fields
     *
     * @return array
     */
    public function getFields() 
    {
        $fields = parent::getFields();
         $fields['active'] = array('type'=>'select','required'=>true,'values'=>array(array('label'=>__('Yes'),'value'=>1),array('label'=>__('No'),'value'=>0)));
        return $fields;
    }
    
    /**
     * Retrieve labels
     *
     * @param  string $key
     * @return string
     */
    public function getLabel($key) 
    {
        switch($key) {
        case 'label' : 
            return __('Request To Quote Settings');break;
        case 'active' : 
            return __('Enable Rfq System');break;
        default : 
            return parent::getLabel($key); break;
        }
    }
}
