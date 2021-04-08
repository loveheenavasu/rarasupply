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
 * @package   Ced_CsDelhivery
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsFedexShipping\Block\Order\Shipment;


class View extends \Magento\Shipping\Block\Adminhtml\View {
	/**
	 *
	 * @param \Magento\Backend\Block\Widget\Context $context        	
	 * @param \Magento\Framework\Registry $registry        	
	 * @param array $data        	
	 */
	public function __construct(\Magento\Backend\Block\Widget\Context $context, \Magento\Framework\Registry $registry, array $data = []) {
		$this->_coreRegistry = $registry;
		parent::__construct ( $context, $registry, $data );
		$this->setData('area','adminhtml');
	}
	
	protected function _construct() 
	{
		//die('========');
		$this->_objectId = 'shipment_id';
		$this->_mode = 'view';
		parent::_construct ();
		$this->buttonList->remove ( 'reset' );
		$this->buttonList->remove ( 'delete' );
		$this->buttonList->remove ( 'save' );
		if (! $this->getShipment ()) {
			return;
		}
		
		$objectmanager = \Magento\Framework\App\ObjectManager::getInstance ();
		$idoforder=$this->getShipment ()->getOrder()->getEntityId();
		$oOrder = $objectmanager->create ( '\Magento\Sales\Model\Order' )->load($idoforder);
		//print_r($oOrder->getShippingDescription());die(' view');
		$directory = $objectmanager->get('\Magento\Framework\Filesystem\DirectoryList');
         
		$shipId = $this->getShipment()->getEntityId();
		//echo $shipId;die;
		$shipmentCollection = $objectmanager->create('\Magento\Sales\Model\Order\Shipment\Track')->getCollection()
		  					->addFieldToFilter('parent_id',$shipId);
		
		$title = '';
		$waybills = 0;  					
		if(count($shipmentCollection->getData())){
			foreach ($shipmentCollection as $tracknum)
		  	{		
	  			$waybills=$tracknum->getNumber();	
	  			$title = $tracknum->getCarrierCode();
	  			$detail=$tracknum->getFedexDetail();	  	
		  	}
		}//echo $title;
		
		if(strpos($title, 'fedex') !== false && $waybills)
		{
			$shipid=$this->getShipment()->getIncrementId(); 
			$trackpath = $directory->getRoot().'/fedex/'.$waybills.'.pdf';
			$codtrackpath = $directory->getRoot().'/fedex/'.$detail.'.pdf';
			/*print_r($trackpath);
			echo '<br>';
			print_r($codtrackpath);*/
			//die;
			if (file_exists($trackpath)){
				$url=$this->getUrl('fedex/').$waybills.'.pdf';
				$this->buttonList->add('generatemanifesto', array(
						'label'     => __('Fedex Label'),
						'onclick'   => "window.open('".$url."','_blank')",
						'target'  =>  '_blank',
						'class'     => 'btn btn-success uptransform',
				),-1);
			}
			
			if (file_exists($codtrackpath)){
				$url=$this->getUrl('fedex/').$detail.'.pdf';
				$this->buttonList->add('shippinglabel', array(
						'label'     => __('Fedex Cod Label'),
						'onclick'   => "window.open('".$url."','_blank')",
						'target'  =>  '_blank',
						'class'     => 'btn btn-success uptransform',
				),-1);
			}
			//$filepath=$objectmanager->get('\Magento\Framework\App\Filesystem\DirectoryList')->getPath('media').'/'.'pdffiles'.'/'.$shipid.'.pdf';
			
			/*if (file_exists($filepath)) {
				$url=$this->getUrl('pub/media').'pdffiles/'.$shipid.'.pdf';
				$this->buttonList->add('submitmanifesto2', array(
						'label'     => __('View Manifesto'),
						'onclick'   => "window.open('".$url."','_blank')",
						'target'  =>  '_blank',
						'class'     => 'btn btn-success uptransform',
						
				),-1);
			}*/

			/*$this->buttonList->add('pickup', array(
					'label'     => __('Pick Up'),
					'onclick'   =>  "window.open('".$this->getUrl('csdelhivery/index/pickuprequest/')."','_blank')",
					'class'     => 'task task-click',
			),-1);*/
		}
	}
	
    /**
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('csorder/shipment/print', ['shipment_id' => $this->getShipment()->getId()]);
    }
}
