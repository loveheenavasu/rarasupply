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
namespace Ced\CsCmsPage\Model;

class Vendorblock extends \Magento\Framework\Model\AbstractModel
{
	const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
	protected function _construct()
	{
		$this->_init('Ced\CsCmsPage\Model\ResourceModel\Vendorblock');
	}
	
}