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
namespace Ced\CsRma\Model\ResourceModel\Rmashipping;

/**
* Collection of Rmashippinginfo
* 
*/
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection 
{
	/**
	* Rmashippinginfo Collection Resource Constructor
	* @return void
	*/
    protected function _construct()
    {
    	$this->_init('Ced\CsRma\Model\Rmashipping', 'Ced\CsRma\Model\ResourceModel\Rmashipping');
    }
}