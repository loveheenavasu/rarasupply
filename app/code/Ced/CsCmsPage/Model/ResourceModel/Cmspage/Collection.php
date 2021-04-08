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
namespace Ced\CsCmsPage\Model\ResourceModel\Cmspage;

class Collection  extends \Magento\Cms\Model\ResourceModel\Page\Collection
{
	protected $_previewFlag;

    /**
     * Declare base table and mapping of some fields
     */
    protected function _construct()
    {
      $this->_init('Ced\CsCmsPage\Model\Cmspage', 'Ced\CsCmsPage\Model\ResourceModel\Cmspage');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
        $this->_map['fields']['store']   = 'store_table.store_id';
    }

    /**
     * deprecated after 1.4.0.1, use toOptionIdArray()
     * 
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('identifier', 'title');
    }

    /**
     * Returns pairs identifier - title for unique identifiers
     * and pairs identifier|page_id - title for non-unique after first
     * 
     * @return array
     */
    public function toOptionIdArray()
    {
        $res = array();
        $existingIdentifiers = array();
        foreach ($this as $item) {
            $identifier = $item->getData('identifier');

            $data['value'] = $identifier;
            $data['label'] = $item->getData('title');
            if (in_array($identifier, $existingIdentifiers)) {
                $data['value'] .= '|' . $item->getData('page_id');
            }
            else {
                $existingIdentifiers[] = $identifier;
            }

            $res[] = $data;
        }

        return $res;
    }

    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    protected function _afterLoad()
    {
        $this->performAfterLoad('ced_cscmspage_vendor_cmspagestore', 'page_id');
        $this->_previewFlag = false;

        return parent::_afterLoad();
    }

   
    
    /**
     * Add filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @return Mage_Cms_Model_Mysql4_Page_Collection
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }
        $this->addFilter('store', array('in' => ($withAdmin ? array(0, $store) : $store)), 'public');
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                array('store_table' => $this->getTable('ced_cscmspage_vendor_cmspagestore')),
                'main_table.page_id = store_table.page_id',
                array()
            )->group('main_table.page_id');
        }
        return parent::_renderFiltersBefore();
    }

    /**
     * Get SQL for get record count.
     * Extra group by strip added.
     *
     * @return Varien_Db_Select
     */
  //  public function getSelectCountSql()
   // {
   //     $countSelect = parent::getSelectCountSql();

   //     $countSelect->reset(Zend_Db_Select::GROUP);
//
   //     return $countSelect;
 //   }
}
