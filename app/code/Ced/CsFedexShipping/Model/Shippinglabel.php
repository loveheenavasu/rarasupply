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

class Shippinglabel extends \Magento\Framework\Model\AbstractModel {
	/**
	 * setting template
	 * @see Varien_Object::_construct()
	 */
    public function _construct() {
        parent::_construct();
        $this->_init('csfedexshipping/index/shippinglabel');
    }

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
    /**
     * Y coordinate
     *
     * @var int
     */
    public $y;

    /**
     * Zend PDF object
     *
     * @var Zend_Pdf
     */
    
    protected $_pdf;

	
    /**
     * Generate Shipment Label Content for each Waybill
     *
     * @param Zend_Pdf_Page $page
     * @param null $store
     */
    public function getContent(&$page, $store = null, $waybill, $order, $pos,$vendorId)
    { //die('in model');
        $vorderOrder = $this->_objectmanager->create('Ced\CsMarketplace\Model\Vorders')->getCollection()
        ->addFieldToFilter('vendor_id',$vendorId)->addFieldToFilter('order_id',$order->getIncrementId())->getFirstItem();
    	
        $vsetting = $this->_objectmanager->create('Ced\CsMarketplace\Model\Vsettings')->getCollection()->addFieldToFilter('vendor_id',$vendorId);           
        $vendor = $this->_objectmanager->create('Ced\CsMarketplace\Model\Vendor')->load($vendorId);
        $warehouseaddress = $vendor->getName();
        $data = array();
        //print_r($vendor->getData());die;
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

        $top         = $pos; //top border of the page
        $widthLimit  = 100; //half of the page width
        $heightLimit = 70; //assuming the image is not a "skyscraper"
        $width=195;
        $height=137;
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

        $y1 = $top - $height;
        $y2 = $top;
        $x1 = 25;
        $x2 = $x1 + $width;
        
        $methodcode = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' || 'checkmo') ? "Post-Paid" :"Pre-Paid";
        $page->drawImage($image, $x1, $y1, $x2, $y2);
		$this->_setFontRegular($page, 8);
		$page->drawText(__('Order # ') . $order->getRealOrderId(), $x1+190, ($y1+25), 'UTF-8');
        $page->drawText(__('Order Date: ') .$this->_localeDate->formatDate($this->_localeDate->scopeDate($order->getStore(),$order->getCreatedAt(),true),\IntlDateFormatter::MEDIUM,false),$x1+190,($y1+15),'UTF-8');

		$codamount = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' || 'checkmo') ? $vorderOrder->getOrderTotal() : "00.00";
		$page->drawText(__('COD Amount ') . $codamount, $x1+190, ($y1+5), 'UTF-8');			
		$page->drawText(__('Total Collectable Amount ') . $vorderOrder->getOrderTotal(), $x1+190, ($y1-5), 'UTF-8');
		$page->drawRectangle(280, $y1-20, 200, $y1-50, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		$this->_setFontBold($page, 9);
		$page->drawText(__('') . $methodcode, $x1+190, ($y1-35), 'UTF-8');
		$this->_setFontRegular($page, 8);
		$font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/web/fedex/font/FRE3OF9X.TTF')
        );
        $page->setFont($font, 30);
		$barcodeImage = "*".trim($waybill)."*";
		$page->drawText($barcodeImage, $x1+370, $y1+12);				
        $this->_setFontRegular($page, 8);
		//$page->drawText("*", $x1+385, $y1+15);
		//$page->drawText("*", $x1+540, $y1+15);
		$page->drawText("AWB#".trim($waybill), $x1+420, $y1+2);
		$this->_setFontBold($page, 9);
		$page->drawText("Ship to:", $x1+390, $y1-15);
		$page->drawText("Ship from:", $x1, $y1-15);
		$this->_setFontRegular($page, 8);
        $page->drawText("Store: ".$url[2], $x1+390, $y1-25);
		$shippingAddress =  $this->_formatAddress($this->addressRenderer->format($order->getShippingAddress(), 'pdf'));			
		$addressy = $y1-35;
		 foreach ($shippingAddress as $value) {
            if ($value !== '') {
                $text = [];
                foreach ($this->string->split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), $x1+390, $addressy, 'UTF-8');
                    $addressy -= 11;
                }
            }
        }
		$addressy = $y1-25;
		//$buyer = $addressy;
		foreach (explode(",", $warehouseaddress) as $value)
		{
			if ($value !== '')
			{
				$value = preg_replace('/<br[^>]*>/i', "\n", $value);
				foreach ($this->string->split($value, 45, true, true) as $_value)
				{
					$page->drawText(strip_tags(trim($_value)), $x1, $addressy, 'UTF-8');	
					$addressy -= 11;
				}
			}
		}
		//$shipper = $addressy;
		//$page->drawLine(5, $buyer-10, $x1+570, $buyer-10);	
		//if($shipper>$buyer)
		    $page->drawLine(5, $addressy-25, $x1+570, $addressy-25);							
       
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