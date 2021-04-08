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


namespace Ced\CsFedexShipping\Model;
use Magento\Framework\App\Filesystem\DirectoryList;
require_once __DIR__ . '/BarcodeGeneratorPNG.php';
class Manifestolabel extends \Magento\Framework\Model\AbstractModel {

    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Customer\Model\Session $customerSession,\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\ObjectManagerInterface $objectInterface, \Magento\Framework\Filesystem $filesystem, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,\Magento\Payment\Helper\Data $paymentData, \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,  \Magento\Framework\Stdlib\StringUtils $string
 ) {
		$this->_session = $customerSession;
		$this->_scopeConfig = $scopeConfig;
        $this->_objectmanager=$objectInterface;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
		$this->resultPageFactory = $resultPageFactory;
        $this->_localeDate = $localeDate;
        $this->_paymentData = $paymentData;
        $this->addressRenderer = $addressRenderer;
        $this->string = $string;
     /*   parent::_construct();*/
        /*$this->_init('csdelhivery/index/manifestolabel');*/
	}

    public function getContent($pdf,$page, $store = null, $waybill, $order,$shipid, $pos,$vendorId)
    {   

        //$vendorId = $this->_session->getVendorId();
        $vorderOrder = $this->_objectmanager->get('Ced\CsMarketplace\Model\Vorders')->getCollection()
        ->addFieldToFilter('vendor_id',$vendorId)->addFieldToFilter('order_id',$order->getIncrementId())->getFirstItem();
        $vendorDetails = $this->_objectmanager->get('Ced\CsMarketplace\Model\Vendor')->load($vendorId);
        $shipDetail =  $this->_objectmanager->get('Magento\Sales\Model\Order\Shipment\Track')->load($shipid,'parent_id');  
        $shipDate = $shipDetail->getCreatedAt();
        $shipDetail = json_decode($shipDetail->getFedexDetail(),true);
        //print_r($shipDetail);die;    
        $meter =$this->_scopeConfig->getValue('carriers/fedex/meter_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        //print_r($shipDetail->getData());die;
        $top = $pos; //top border of the page
        $widthLimit  = 120; //half of the page width
        $heightLimit = 70; //assuming the image is not a "skyscraper"
        $width=120;
        $height=15;
        $ratio = $width / $height;
        if ($ratio > 1 && $width > $widthLimit)
        {
            $width  = $widthLimit;
            $height = $width / $ratio;
        } elseif ($ratio < 1 && $height > $heightLimit)
        {
            $height = $heightLimit;
            $width  = $height * $ratio;
        } elseif ($ratio == 1 && $height > $heightLimit)
        {
            $height = $heightLimit;
            $width  = $widthLimit;
        }
        
        $logoTop=$top-5;
        
        $y1 = $logoTop - $height;
        $y2 = $logoTop;
        $x1 = 25;
        $x2 = $x1 + $width;

        $vsetting = $this->_objectmanager->create('Ced\CsMarketplace\Model\Vsettings')->getCollection()->addFieldToFilter('vendor_id',$vendorId);           
        $vendor = $this->_objectmanager->create('Ced\CsMarketplace\Model\Vendor')->load($vendorId);
        $warehouseaddress = $vendor->getName();
        $data = array();
        //print_r($vsetting->getData());die;
        foreach($vsetting as $key=> $value){
            if($value['key'] == 'shipping/address/country_id')
                $data['country'] = $this->_objectmanager->create('\Magento\Directory\Model\Country')->loadByCode($value['value'])->getName();
            if($value['key'] == 'shipping/address/postcode')
                $data['zip'] = $value['value'];
            if($value['key'] == 'shipping/address/region_id')
                $data['state'] = $this->_objectmanager->create('\Magento\Directory\Model\Region')->load($value['value'])->getName();
            if($value['key'] == 'shipping/address/city')
                $data['city'] = $value['value'];
            if($value['key'] == 'shipping/address/phoneno')
                $data['phone'] = $value['value'];
            
        }
        
        $warehouseaddress .= ', '.$vendor->getAddress().', '.$data['city'].', '.$data['state'].', '.$data['zip'].' '.$data['country'].', T- '.$vendor->getContactNumber();
        //$image = $this->_scopeConfig->getValue('sales/identity/logo');
        
        $imagePath='lib/web/fedex/image/fedex.png';
            
        //echo $imagePath;die;
        
        $url = explode('/',$this->_objectmanager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl());
        //print_r($url);die;
        //echo $url ;die;
        $image       = \Zend_Pdf_Image::imageWithPath($this->_rootDirectory->getAbsolutePath($imagePath));
        
         if ($image) {
            //$imagePath = '/sales/store/logo/' . $image;
            //$image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath));
            //$page->drawImage($image, $x1, $y1, $x2, $y2);
        }

        $y11=$top-30;
        
        $ServiceType = 'STANDARD OVERNIGHT';
        if(strpos($order->getShippingMethod(),'EXPRESS_SAVER')!==false)
            $ServiceType = 'EXPRESS SAVER';

        $this->_setFontRegular($page, 8);
        $page->drawText(__('Order # ') . $order->getRealOrderId(), $x1+150, ($y11+25), 'UTF-8');
        $page->drawText(__('Order Date: ') .$this->_localeDate->formatDate($this->_localeDate->scopeDate($order->getStore(),$order->getCreatedAt(),true),\IntlDateFormatter::MEDIUM,false),$x1+150,($y11+17),'UTF-8');
        $codamount = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' || 'checkmo' ) ? $vorderOrder->getOrderTotal() : "00.00";
        //$page->drawText(__('COD Amount ') . $codamount, $x1+200, ($y11+9), 'UTF-8');         
        $page->drawText(__('Ship Date ') . $shipDate, $x1+150, ($y11+9), 'UTF-8');
        $this->_setFontBold($page, 10);
        $this->_setFontBold($page, 9);
        
        $page->drawText(__('BILL T/C : SENDER') , $x1+150, ($y11-10), 'UTF-8');
        $page->drawText(__('BILL D/T : SENDER') , $x1+150, ($y11-20), 'UTF-8');
        $page->drawText(__('Fedex Meter : ') . $meter, $x1+150, ($y11-30), 'UTF-8');
        $page->drawText('Carrier : FedEx', $x1, ($y11-40), 'UTF-8');
        $aa=$order->getStatus();
                
        $top = $pos; //top border of the page  
        $top = $pos-120;
        if($codamount)              
            $top = $pos-200;

        /* shipping address starts */
        
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 285, ($top - 25));
        $page->drawRectangle(285, $top, 570, ($top - 25));

        /* Calculate blocks info */

        /* Billing Address */
        //$billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));

        /* Payment */
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);
       
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($this->addressRenderer->format($order->getShippingAddress(), 'pdf'));
            $shippingMethod = $order->getShippingDescription();
        }      

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 9);
        $page->drawText(__('Ship From:'), 35, ($top - 15), 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(__('Ship To:'), 300, ($top - 15), 'UTF-8');
        } else {
            $page->drawText(__('Payment Method:'), 300, ($top - 15), 'UTF-8');
        }
        
        $addressesHeight=60;
    
        //$addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            //$addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
            $addressesHeight=60;
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, ($top - 25), 285, $top - 50 - $addressesHeight);
        $page->drawRectangle(285, ($top - 25), 570, $top - 50 - $addressesHeight);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 8);
        $this->y = $top - 40;
        $addressesStartY = $this->y;
        //$v_model = $this->_objectmanager->get('Ced\CsMarketplace\Model\Vendor')->load($vendorId);
        //$shippingFrom=array($v_model->getName(), $v_model->getAddress(), $v_model->getCity().", ".$v_model->getZipCode(), $v_model->getCountryId(), "T: ".$v_model->getContactNumber(),'TIN Number: '. $v_model->getTinNumber());
        
        foreach (explode(",", $warehouseaddress) as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 10;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()){
            $this->y = $addressesStartY;
            

            
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    foreach ($this->string->split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 295, $this->y, 'UTF-8');
                        $this->y -= 10;
                    }
                }
            }
        }

        //$imagePath = 'pub/static/frontend/Ced/ced/en_US/Ced_CsDelhivery/media/delhivery/image/delhivery.jpg';
        //$image     = \Zend_Pdf_Image::imageWithPath($this->_rootDirectory->getAbsolutePath($imagePath));

        $top         = $pos; //top border of the page
        $widthLimit  = 100; //half of the page width
        $heightLimit = 70; //assuming the image is not a "skyscraper"
                        
        $width=195;
        $height=137;
        $ratio = $width / $height;
        if ($ratio > 1 && $width > $widthLimit) {
            $width  = $widthLimit;
            $height = $width / $ratio;
        } elseif ($ratio < 1 && $height > $heightLimit) {
            $height = $heightLimit;
            $width  = $height * $ratio;
        } elseif ($ratio == 1 && $height > $heightLimit) {
            $height = $heightLimit;
            $width  = $widthLimit;
        }

        $y1 = $top - $height;
        $y2 = $top;
        $x1 = 25;
        $x2 = $x1 + $width;
        
        $methodcode = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' ) ? "COD" :"Pre-Paid";
        $page->drawImage($image, $x1, $y1+32, $x2, $y2);
        $items = $order->getAllItems();
        $x = 30;
        $this->y = 550;
        
        
        //$page->drawText("MANIFEST REPORT::". $x + 15, $this->y-119, 8);
        
        $codAwbDetail = $shipDetail['cod'];
        $this->_setFontBold($page, 11);
        $page->drawText("TRK# :".chunk_split(trim($codAwbDetail['awb']),4,' '), $x1+320, $y1+68); 
        $page->drawText("FORM ID. ".trim($shipDetail['form_id']), $x1+430, $y1+68);
        if($codAwbDetail)
            $page->drawText("COD", $x1+520, $y1+68);
        $page->drawText($ServiceType , $x1+320, $y1+56, 'UTF-8');
        $page->drawText($shipDetail['service_area'] , $x1+470, $y1+56, 'UTF-8');
        $this->_setFontBold($page, 13);
        
        $page->drawText(trim($shipDetail['prefix']).' '.trim($shipDetail['rout_code']), $x1+320, $y1+44);
        $this->_setFontBold($page, 11);
        $page->drawText('-IN         '.$shipDetail['airport_id'].'          '.$shipDetail['postal_code'], $x1+400, $y1+44);
        
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/web/fedex/font/FRE3OF9X.TTF')
        );
        $page->setFont($font, 30);
        $fontPath = $this->_rootDirectory->getAbsolutePath('lib/web/fonts/opensans/regular/opensans-400.ttf');
        /*$page->setFont($font, 50);
        $Barcodeimagepath= $this->_rootDirectory->getAbsolutePath('pub/media/');                              
        $barcodeOptions = array(
                'text' => $waybill,
                'drawtext'=>false,
        ); 

        $rendererOptions = array();
        $imageResource =    \Zend_Barcode::draw(
            'code128', 'image', $barcodeOptions, $rendererOptions
        );
             
        imagejpeg($imageResource, $Barcodeimagepath.'barcode.jpg', 100);
        imagedestroy($imageResource);
        $image = \Zend_Pdf_Image::imageWithPath($Barcodeimagepath.'barcode.jpg');
             
        $page->drawImage($ , $x1+300,$y1+20,$x1+500 ,$y1-30 );*/
        //barcode generation with image
        $generatorPNG = new BarcodeGeneratorPNG();
        $Barcodeimagepath = $generatorPNG->getBarcode($shipDetail['barcode'],1);
        $image = \Zend_Pdf_Image::imageWithPath($Barcodeimagepath);
        $page->drawImage($image, $x1+310, $y1+35, $x1+550, $y1-37);
        //echo '<img src="data:image/png;base64,' . base64_encode($generatorPNG->getBarcode('781662156665', $generatorPNG::TYPE_CODE_128_C,1.5,100)) . '">';
       
        /*$font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/web/fedex/font/FRE3OF9X.TTF')
        );
        $fontPath = $this->_rootDirectory->getAbsolutePath('lib/web/fonts/opensans/regular/opensans-400.ttf');
        
        \Zend_Barcode::setBarcodeFont($fontPath);
        $rendererOptions = array('leftOffset' => $x1+300,'topOffset'=>45 );
        $barcodeOptions = array(
                            'text' => $shipDetail['barcode'],
                            'barHeight'=> 50,
                            'factor'=> 1.9,
                            'drawtext'=>false,
                            'addLeadingZeros'=>true,
                            'withQuietZones'=>true,
                            'stretchText'=>true
                    
                    );
        $renderer = \Zend_Barcode::factory(
                        'code128', 'pdf', $barcodeOptions, $rendererOptions
                )->setResource($pdf)->draw();
        $this->_setFontBold($page, 9);*/

        if($codamount){
            $this->_setFontBold($page, 10);
            $page->drawText(__('COD CASH, PLEASE COLLECT INR  ') . $vorderOrder->getOrderTotal(), $x1, ($y11-60), 'UTF-8');
            $this->_setFontBold($page, 11);
            $page->drawText("TRK# :".chunk_split(trim($waybill),4,' '), $x1, $y1-35);
            $page->drawText("FORM ID. ".trim($codAwbDetail['form_id']), $x1+120, $y1-35);
            $page->drawText('PRIORITY OVERNIGHT' , $x1, $y1-48, 'UTF-8');
            $page->drawText('COD RETURN' , $x1+120, $y1-48, 'UTF-8');


            /*$fontPath = $this->_rootDirectory->getAbsolutePath('lib/web/fonts/opensans/regular/opensans-400.ttf');   
            \Zend_Barcode::setBarcodeFont($fontPath);
            $rendererOptions = array('leftOffset' => $x1-10,'topOffset'=>135 );
            $prinatableString = '';
            $waybill = (string)$codAwbDetail['awb'];
            //echo $waybill[1];die;
            for ($pos = 0; $pos < strlen($waybill); $pos++) {
                $prinatableString .= '0'.$waybill[$pos];

            }
            $prinatableString = chunk_split(trim($prinatableString),8,' ');
            //echo $prinatableString;die;
            $barcodeOptions = array(
                                'text' => $prinatableString,
                                'barHeight'=> 76,
                                'factor'=> 1.9,
                                'drawtext'=>false,
                                'addLeadingZeros'=>true,
                                'withQuietZones'=>true,
                                'stretchText'=>true
                        
                        );
            $renderer = \Zend_Barcode::factory(
                            'code128', 'pdf', $barcodeOptions, $rendererOptions
                    )->setResource($pdf)->draw();*/


            $font = \Zend_Pdf_Font::fontWithPath(
                        $this->_rootDirectory->getAbsolutePath('lib/web/fedex/font/FRE3OF9X.TTF')
                    );
            $page->setFont($font, 30);
            $generatorPNG = new BarcodeGeneratorPNG();
            $Barcodeimagepath = $generatorPNG->getBarcode($codAwbDetail['barcode'],1);
            $image = \Zend_Pdf_Image::imageWithPath($Barcodeimagepath);
            //print_r($Barcodeimagepath);die;
            $page->drawImage($image, $x1, $y1-53, $x1+240, $y1-125);
        }

        $this->y=$this->y;
        if($codamount)          
            $this->y=$this->y+85-160;
        $addressy = $this->y+20; 
        //echo $addressy;die;
        $namey = $this->y;
        $this->_setFontBold($page, 8);
        $page->drawRectangle($x-5, $addressy - 8, $page->getWidth()-25, $addressy + 15, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
       
        $page->drawText('S.NO', $x + 0, $addressy, 'UTF-8');
        $page->drawText('Product Name', $x + 90, $addressy, 'UTF-8'); 
        $page->drawText('SKU', $x + 260, $addressy, 'UTF-8');
        $page->drawText('Qty', $x + 380, $addressy, 'UTF-8');
        $page->drawText('Price', $x + 470, $addressy, 'UTF-8');
        
        $count = 0;
        $products = array();
        $prodCost = array();
        $quantity = 0;
        $weight = 0;
        $productCount=0;
        $vendorId = $this->_session->getVendorId();
        
        $productTotal=array();
        $productTotalNumber=1;
   
        $skuValues=array();
        $quantityValues=array();

        foreach ($items as $item){ 
            if ($item->getQtyOrdered() != $item->getQtyCanceled() && ($item->getParentId() == null || $item->getParentId() == 0)){
                $productId = $item->getProductId();
            
                $product = $this->_objectmanager->get('\Magento\Catalog\Model\Product')->load($productId);
                $productVendor = $this->_objectmanager->get('Ced\CsMarketplace\Model\Vproducts')->getVendorIdByProduct($productId);
                if($vendorId != $productVendor || $item->getProductType() == 'configurable'){
                    continue;
                }                                    
                $products[] = $item->getName();                    
                $skuValues[] = $item->getSku();
                $prodCost[] = $item->getRowTotal();        
                $quantity = $quantity + ($item->getQtyOrdered()- $item->getQtyCanceled()) ;
                $quantityValues[]=$item->getQtyOrdered()- $item->getQtyCanceled();
                $weight = $weight + $item->getWeight(); 
                $productTotal[]=$productTotalNumber;
                $productTotalNumber++; 
            }                  
        } 
        //echo  $weight;die;
        $this->_setFontBold($page, 9);
        $page->drawText(__('Weight : ') . $weight.'KG', $x1+150, ($y11-40), 'UTF-8');
        $productTotalOriginal=$productTotalNumber;                
        $masterArray=array('products'=>$products,'skuValues'=>$skuValues,'quantityValues'=>$quantityValues,'prodCost'=>$prodCost);
        $this->_setFontRegular($page, 8);
        foreach ($masterArray['products'] as $key=>$value){
           
            //print '<pre>'; print_R($products); die;
                if ($product !== '') {
                    /* print name starts */
                    //$nameyOriginal=$namey-10;
                    $nameyOriginal=$namey;
                    $text = array();
                    foreach ($this->string->split($value, 42, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {                             
                        $page->drawText(strip_tags(ltrim($part)), $x+50, $namey - ($productCount * 10), 'UTF-8');
                        $namey -= 8;
                    }
                    /* print name ends */
                    
                    /* print serial starts */
                    $namey=$nameyOriginal;
                    $textSerial = array();
                    foreach ($this->string->split($key+1, 32, true, true) as $_value) {
                        $textSerial[] = $_value;
                    }
                    foreach ($textSerial as $part) {                               
                        $page->drawText(strip_tags(ltrim($part)), $x+10, $namey -($productCount * 10), 'UTF-8');
                        $namey -= 8;
                    }
                    /* print serial ends */
                    
                    /* print sku starts */
                    $namey=$nameyOriginal;
                    $textSku = array();
                    foreach ($this->string->split($masterArray['skuValues'][$key], 42, true, true) as $_value) {
                        $textSku[] = $_value;
                    }
                    foreach ($textSku as $part) {                              
                        $page->drawText(strip_tags(ltrim($part)), $x+220, $namey -($productCount * 10), 'UTF-8');
                        $namey -= 8;
                    }
                    /* print sku ends */
                    
                    /* print quantity starts */
                    $namey=$nameyOriginal;
                    $textQuantity = array();
                    foreach ($this->string->split($masterArray['quantityValues'][$key], 10, true, true) as $_value) {
                        $textQuantity[] = $_value;
                    }
                    foreach ($textQuantity as $part) {                             
                        $page->drawText(strip_tags(ltrim($part)), $x+380, $namey -($productCount * 10), 'UTF-8');
                        $namey -= 8;
                    }
                    /* print quantity ends */

                    /* print product price starts */
                    $namey=$nameyOriginal;
                    $rowCost = array();
                    foreach ($this->string->split($masterArray['prodCost'][$key], 10, true, true) as $_value) {
                        $rowCost[] = $_value;
                    }
                    foreach ($rowCost as $part) {                             
                        $page->drawText(strip_tags(ltrim($part)), $x+470, $namey -($productCount * 10), 'UTF-8');
                        $namey -= 8;
                    }
                    /* print product price  ends */
                }
            $productCount++;
        }
        
        
        $count++;

        $page->drawLine($x -5, $this->y+35, $x-5, $namey - ($productCount * 10) + 8 );
        $page->drawLine($x + 30, $this->y+35, $x + 30, $namey - ($productCount * 10) + 8 );
        $page->drawLine($x + 200, $this->y+35, $x + 200, $namey - ($productCount * 10) + 8 );
        $page->drawLine($x + 360, $this->y+35, $x + 360, $namey - ($productCount * 10) + 8 );
        $page->drawLine($x + 430, $this->y+35, $x + 430, $namey - ($productCount * 10) + 8 );
        $page->drawLine($page->getWidth()-25, $this->y+35, $page->getWidth()-25, $namey - ($productCount * 10) + 8 );
        
         
        $page->drawLine($x - 5, $namey - ($productCount * 10) + 8, $page->getWidth()-25, $namey - ($productCount * 10) + 8 );         
        
        $this->_setFontRegular($page, 9);
        $page->drawText('This is system generated document and hence does not require signature.', $x + 5, $namey - ($productCount * 10) - 25, 'UTF-8');
        $page->drawText('Terms and Conditions : Subject to the "Conditions of Carriage", which limits the liability of FedEx for loss, delay or damage to the consignment.', $x+5, $namey - ($productCount * 10) - 38, 'UTF-8');
        $page->drawText('Visit "www.fedex.com/in" to view the Conditions of Carriage.', $x+100, $namey - ($productCount * 10) - 55, 'UTF-8');
    }   
    
    /**
     * Set PDF object
     *
     * @param  Zend_Pdf $pdf
     * @return Mage_Sales_Model_Order_Pdf_Abstract
     */
    protected function _setPdf(Zend_Pdf $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Retrieve PDF object
     *
     * @throws Mage_Core_Exception
     * @return Zend_Pdf
     */
    protected function _getPdf()
    {
        if (!$this->_pdf instanceof Zend_Pdf) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please define PDF object before using.'));
        }

        return $this->_pdf;
    }

    /**
     * Return PDF document
     *
     * @param  array $shipments
     * @return Zend_Pdf
     */
/*    public function getPdf()
    {
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        
        return $pdf;
    }  */ 

    /**
     * Format address
     *
     * @param  string $address
     * @return array
     */
    protected function _formatAddress($address)
    {
        $return = [];
        foreach (explode('|', $address) as $str) {
            foreach ($this->string->split($str, 45, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    /**
     * Set font as regular
     *
     * @param  Zend_Pdf_Page $object
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  \Zend_Pdf_Page $object
     * @param  int $size
     * @return \Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_It-2.8.2.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

}