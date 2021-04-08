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
 * @package     Ced_CsDeal
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Model\ResourceModel;
 
class Deal extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('ced_csdeal_deal', 'deal_id');
    }
    
    public function getVendorDealProductIds($id) {
    	$DealTable = $this->getTable('ced_csdeal_deal');
    	$dbh    = $this->_getConnection('ced_csdeal_deal');
    	$select = $dbh->select()->from($DealTable,array('product_id'))
    	->where("vendor_id ={$id}");
    	return $dbh->fetchCol($select);
    }
}