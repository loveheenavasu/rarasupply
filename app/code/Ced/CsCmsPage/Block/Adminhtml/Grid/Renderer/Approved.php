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
namespace Ced\CsCmsPage\Block\Adminhtml\Grid\Renderer;
 
class Approved extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {
 
	/**
	 * Render approval link in each vendor row
	 * @param Varien_Object $row
	 * @return String
	 */
	public function render(\Magento\Framework\DataObject $row) {
		
	//	print_r($row->getData()); die("cckjb");
	//die("kn");
		$html = '';
		if($row->getBlockId()!='' && $row['is_approve'] != 1) {	
			$url =  $this->getUrl('*/*/massStatus', array('block_id' => $row->getBlockId() , 'status'=>'approved', 'inline'=>1));
			$html .= '<a href="javascript:void(0);" onclick="deleteConfirm(\''.__('Are you sure you want to Approve?').'\', \''. $url . '\');" >'.__('Approve').'</a>';  
		} 
			
		if($row->getBlockId()!='' && $row['is_approve'] != 0) {
			if(strlen($html) > 0) $html .= ' | ';
			$url =  $this->getUrl('*/*/massStatus', array('block_id' => $row->getBlockId(), 'status'=>'disapproved', 'inline'=>1));
			$html .= '<a href="javascript:void(0);" onclick="deleteConfirm(\''.__('Are you sure you want to Disapprove?').'\', \''. $url . '\');" >'.__('Disapprove')."</a>";
		}
		
		return $html;
	}
}