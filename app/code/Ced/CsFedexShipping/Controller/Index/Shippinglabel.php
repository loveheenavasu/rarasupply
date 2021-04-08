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

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
/**
 * Class Track
 */
class Shippinglabel extends \Magento\Framework\App\Action\Action {
	
	/**
	 *
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;
	protected $_session;
	protected $_scopeConfig;
	protected $_objectmanager;
	/**
	 *
	 * @param \Magento\Framework\App\Action\Context $context        	
	 * @param
	 *        	\Magento\Framework\View\Result\PageFactory resultPageFactory
	 */
	public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Customer\Model\Session $customerSession,\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\ObjectManagerInterface $objectInterface, FileFactory $fileFactory) {
		parent::__construct ( $context );
		$this->_session = $customerSession;
		$this->_scopeConfig = $scopeConfig;
		$this->_objectmanager=$objectInterface;
		$this->_fileFactory = $fileFactory;
		$this->resultPageFactory = $resultPageFactory;
	}
	/**
	 * Default track shipment page
	 *
	 * @return void
	 */
	public function execute() {
		$vid = $this->_session->getVendorId();
		$shipmentId = $this->getRequest()->getPost('shipment_ids');
	  	$shipmentId = $this->getRequest()->getParam('shipment_ids');
	  	$vendorId=$this->_session->getVendorId();;
		$shipmentId = explode(",", $shipmentId);
		foreach($shipmentId as $shipmentIds)
		{
			$z=$this->_objectmanager->get("\Magento\Sales\Model\Order\Shipment")->load($shipmentIds)->getOrder()->getIncrementId();
		  	$shipment2 = $this->_objectmanager->get("\Magento\Sales\Model\Order\Shipment")->load($shipmentIds);
		  	$void = $this->_objectmanager->get('\Ced\CsMarketplace\Model\Vorders')->load($z, 'order_id')->getVendorId();
		  	$vendoremail = $this->_objectmanager->get('\Ced\CsMarketplace\Model\Vendor')->load($void)->getEmail();
		  	$vendorname = $this->_objectmanager->get('\Ced\CsMarketplace\Model\Vendor')->load($void)->getName();
		  	$vorderid = $z;
		  	$order=$this->_objectmanager->get("\Magento\Sales\Model\Order")->loadByIncrementId($vorderid);
		  	$shipmentCollection = $this->_objectmanager->get('\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection')
		  	->setOrderFilter($order)
		  	->load();
		  	foreach ($shipmentCollection as $shipment1){
		  		foreach($shipment1->getAllTracks() as $tracknum)
		  		{
		  			//print_r($tracknum->getData());die;
		  			$waybills[$tracknum->getOrderId()]=$tracknum->getNumber();
		  		}
		  	
		  	}
		}

		//print_r($waybills);die('in fedex');
		
		$flag = false;
		if (!empty($waybills))
		{
			$labelperpage = 3;
			$totalpages = sizeof($waybills)/$labelperpage;   			
	        $pdf = new \Zend_Pdf();
	        $style = new \Zend_Pdf_Style();		
			for ($page_index = 0; $page_index <= $totalpages; $page_index++)
	        {
				$page = new \Zend_Pdf_Page(\Zend_Pdf_Page::SIZE_A4 );
				$pdf->pages[] = $page;
			}
			$pagecounter = 0;
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
				if($i%$labelperpage == 0)
				{
					$pagecounter++; // Set to use new page
					$y = 830; // Set position for first label on new page
				}	
				//$pdf->pages[$pagecounter];
				$shipments = $this->_objectmanager->get('\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection')->setOrderFilter($orderid)->load();
				if ($shipments->getSize())
				{
					
						$flag = true;
						foreach ($shipments as $shipment)
			  			{	
			  				if(($shipment->getOrder()!=NULL)){
			  					//$filenametopath=$shipment->getIncrementId().'.pdf';
			  					$this->_objectmanager->get('\Ced\CsFedexShipping\Model\Shippinglabel')->getContent($pdf->pages[$pagecounter], $shipment->getStore(), $waybill, $shipment->getOrder(),$y, $vid);
			  				}	
			  			}			
					
				}
				
				$y = $y-200;
							
			}
				
			if ($flag)
			{
				
				$date = $this->_objectmanager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                return $this->_fileFactory->create(
                    'shippinglabel'. $date . '.pdf',
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );	 
			} 
			else
			{
				$this->messageManager->addErrorMessage(__('There are no printable shipping labels related to selected shipment.'));
				$this->_redirect('*/*/');
			}
		}
		$this->_redirect('csorder/shipment/index');
	}
}