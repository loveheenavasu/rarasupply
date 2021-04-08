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

class Block extends \Magento\Framework\Model\AbstractModel
{
	const CACHE_TAG     = 'cms_block';
    protected $_cacheTag= 'cms_block';
    
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
	protected function _construct()
	{
		$this->_init('Ced\CsCmsPage\Model\ResourceModel\Block');
	}
	
	public function getAvailableStatuses()
	{
		return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
	}
    /**
     * Prevent blocks recursion
     *
     * @throws Mage_Core_Exception
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $needle = 'block_id="' . $this->getBlockId() . '"';
        if (false == strstr($this->getContent(), $needle)) {
            return parent::_beforeSave();
        }
        Mage::throwException(
            Mage::helper('cms')->__('The static block content cannot contain  directive with its self.')
        );
    }
}