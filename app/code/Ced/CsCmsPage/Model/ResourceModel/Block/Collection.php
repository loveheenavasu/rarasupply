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
namespace Ced\CsCmsPage\Model\ResourceModel\Block;

class Collection  extends \Magento\Cms\Model\ResourceModel\Block\Collection
{

	/**
	 * @var string
	 */
	protected $_idFieldName = 'block_id';
	
	/**
	 * Perform operations after collection load
	 *
	 * @return $this
	 */
	protected function _afterLoad()
	{
		$this->performAfterLoad('ced_cscmspage_vendor_blockstore', 'block_id');
	
		return parent::_afterLoad();
	}
	
	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Ced\CsCmsPage\Model\Block', 'Ced\CsCmsPage\Model\ResourceModel\Block');
		 $this->_map['fields']['page_id'] = 'main_table.page_id';
        $this->_map['fields']['store']   = 'store_table.store_id';
	}
	
	/**
	 * Returns pairs block_id - title
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return $this->_toOptionArray('block_id', 'title');
	}
	
	/**
	 * Add filter by store
	 *
	 * @param int|array|\Magento\Store\Model\Store $store
	 * @param bool $withAdmin
	 * @return $this
	 */
	public function addStoreFilter($store, $withAdmin = true)
	{
		$this->performAddStoreFilter($store, $withAdmin);
	
		return $this;
	}
	
	/**
	 * Join store relation table if there is store filter
	 *
	 * @return void
	 */
	
	public function setFirstStoreFlag($flag = false)
	{
		$this->_previewFlag = $flag;
		return $this;
	}
	
	protected function _renderFiltersBefore()
	{
		$this->joinStoreRelationTable('ced_cscmspage_vendor_blockstore', 'block_id');
	}
	
	
	
	
}