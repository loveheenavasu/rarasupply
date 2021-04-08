<?php

namespace Ced\Advertisement\Block\Adminhtml\Blocks\Renderer;
 
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {
 
	/**
	 * Render approval link in each vendor row
	 * @param Varien_Object $row
	 * @return String
	 */
	public function render(\Magento\Framework\DataObject $row) {
		$html = '';
		if($row->getId()!='' && $row->getStatus() != 1) {	
			$url =  $this->getUrl('*/*/massStatus', array('id' => $row->getId(), 'status'=>1, 'inline'=>1));
			$html .= '<a href="javascript:void(0);" onclick="deleteConfirm(\''.__('Are you sure you want to Approve?').'\', \''. $url . '\');" >'.__('Approve').'</a>';  
		} 
				
		if($row->getId()!='' && $row->getStatus() != 0) {
			if(strlen($html) > 0) $html .= ' | ';
			$url =  $this->getUrl('*/*/massStatus', array('id' => $row->getId(), 'status'=>0, 'inline'=>1));
			$html .= '<a href="javascript:void(0);" onclick="deleteConfirm(\''.__('Are you sure you want to Disapprove?').'\', \''. $url . '\');" >'.__('Disapprove')."</a>";
		}
		
		return $html;
	}
}