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
 * @package     Ced_CsDelhivery
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsFedexShipping\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Class Track
 */
class Manifestolabel extends \Ced\CsMarketplace\Controller\Vendor {
	
	/**
	 *
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	/*protected $resultPageFactory;
	protected $_session;
	protected $_scopeConfig;
	protected $_objectmanager;
	protected $_directory;*/
	/**
	 *
	 * @param \Magento\Framework\App\Action\Context $context        	
	 * @param
	 *        	\Magento\Framework\View\Result\PageFactory resultPageFactory
	 */
	/*public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Customer\Model\Session $customerSession,\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\ObjectManagerInterface $objectInterface,  \Magento\Framework\UrlInterface $url, FileFactory $fileFactory, DirectoryList $directory ) {
		parent::__construct ( $context );
		$this->_session = $customerSession;
		$this->_scopeConfig = $scopeConfig;
		$this->_objectmanager=$objectInterface;
		$this->resultPageFactory = $resultPageFactory;
		$this->url = $url;
		$this->_fileFactory = $fileFactory;
		$this->directory = $directory;
	}*/
	/**
	 * Default track shipment page
	 *
	 * @return void
	 */

	public function execute() { //die('==');
		if(!$this->_getSession()->getVendorId())
			return;
		$objectManager = $this->_objectManager;
		$shipmentId = $this->getRequest()->getPost('shipment_ids');
	  	$shipmentId = $this->getRequest()->getParam('shipment_ids');
	  	$shipmentId = explode(",", $shipmentId);
	  	$vid = $this->_getSession()->getVendorId();
	  	foreach($shipmentId as $shipmentIds)
	  	{
	  		$track = $this->_objectManager->get("\Magento\Sales\Model\Order\Shipment\Track")->getCollection()->addFieldToFilter('parent_id',$shipmentIds);
		  	foreach ($track as $tracknum)
		  	{		
	  			$waybills=$tracknum->getNumber();	  			
	  			$detail=$tracknum->getFedexDetail();	  	
		  	}
		  	//print_r($track->getData());die('---');
		  	/*$z=$this->_objectManager->get("\Magento\Sales\Model\Order\Shipment")->load($shipmentIds)->getOrder()->getIncrementId();
		  	$shipment2 = $this->_objectManager->get("\Magento\Sales\Model\Order\Shipment")->load($shipmentIds);
		  	$void = $this->_objectManager->get('\Ced\CsMarketplace\Model\Vorders')->load($z, 'order_id')->getVendorId();
		  	$vendoremail = $this->_objectManager->get('\Ced\CsMarketplace\Model\Vendor')->load($void)->getEmail();
		  	$vendorname = $this->_objectManager->get('\Ced\CsMarketplace\Model\Vendor')->load($void)->getName();
		  	$vorderid = $z;
		  	$order=$this->_objectManager->get("\Magento\Sales\Model\Order")->loadByIncrementId($vorderid);
		  	$shipmentCollection = $this->_objectManager->get('\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection')
		  	->setOrderFilter($order)
		  	->load();
		  	foreach ($shipmentCollection as $shipment1){
		  		foreach($shipment1->getAllTracks() as $tracknum)
		  		{
		  			$waybills[$tracknum->getOrderId()]=$tracknum->getNumber();
		  		}
		  	
		  	}*/
	  	}
	  	if($waybills)
	  	{
	  		//print_r($detail);die;
	  		header("content-type: application/pdf");
        	header("Content-Disposition:inline;filename=$waybills.pdf");
        	print_r(base64_decode($detail));//die;
 		
	  	}
	  	


	  	
	  
	  	$flag = false;
	  	if (!empty($waybills))
	  	{
	  		$labelperpage=1;
	  		$totalpages = sizeof($waybills)/$labelperpage;
	  		$pdf = new \Zend_Pdf ();
	  		$style = new \Zend_Pdf_Style ();
	  		for ($page_index = 0; $page_index<$totalpages; $page_index++)
	  		{
		  		$page = new \Zend_Pdf_Page(\Zend_Pdf_Page::SIZE_A4 );
		  		$pdf->pages[] = $page;
	  		}

	  		$pagecounter = -1;
	  		$i=0; $y=830;
	  		foreach ($waybills as $orderid=>$waybill)
	  		{
		  		/*$awb = $this->_objectmanager->get('Ced\Delhivery\Model\Awb')->getCollection()->addFieldToFilter('awb',trim($waybill))->addFieldToFilter('vendor_id',$vid)->getData();
				if(empty($awb))
				{
					continue;				
				}
				foreach($awb as $value)
				{
					$orderid=$value['orderid'];
					$shipment_id = $value['shipment_id'];
				}*/
			  	$i++;
			  	// check if next page;
			  	if($i%$labelperpage== 0)
			  	{
				  	$pagecounter++; 
				  	$y = 830; 
			 	}

			  	$shipments = $this->_objectManager->get('\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection')->setOrderFilter($orderid)->load();
			  	//print_r($pagecounter);
			  	
		  		if ($shipments->getSize())
		  		{
		  			$flag = true; 
		  			foreach ($shipments as $shipment)
		  			{	
		  				if(($shipment->getOrder()!=NULL)){
		  					$filenametopath=$shipment->getIncrementId().'.pdf';
		  					$this->_objectManager->get('\Ced\CsFedexShipping\Model\Manifestolabel')->getContent($pdf,$pdf->pages[$pagecounter], $shipment->getStore(), $waybill, $shipment->getOrder(),$shipment->getId(),$y, $vid);
		  					
		  				}	
		  			}
  				}
  				// Set position for the next label on same page
  				$y = $y-190;
	  	
	  		}
			if ($flag)
			{
				$date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                return $this->_objectManager->get('Magento\Framework\App\Response\Http\FileFactory')->create(
                    $filenametopath . $date . '.pdf',
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );	  						  			
			} 
			else
			{
				
	  			$this->messageManager->addErrorMessage(__('There are no printable shipping labels related to selected waybills.'));
	  			$this->_redirect('*/*/');
			}
		}
		$this->_redirect('csorder/shipment/index');
	}
}