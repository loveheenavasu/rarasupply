<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsRfq
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Block\Quotes;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ProductFactory;
use Ced\RequestToQuote\Helper\Data as Helper;
use Magento\Customer\Model\Session;
use Ced\CsMarketplace\Model\Vproducts;
class AddToQuote extends \Ced\RequestToQuote\Block\Quotes\AddToQuote {

    /**
     * AddToQuote constructor.
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param Helper $helper
     * @param Session $session
     * @param array $data
     */
    
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        Helper $helper,
        Session $session,
        Vproducts $vproducts,
        array $data = []
        )
    {
        $this->productFactory = $productFactory;
        $this->helper = $helper;
        $this->session = $session;
        $this->vproducts = $vproducts;
        parent::__construct($context, $productFactory, $helper, $session,$data);
        if($this->getProduct()->getTypeId()=='configurable'){
            $this->setTemplate("Ced_CsRfq::quotes/configaddtoquote.phtml");
        }else{
            $this->setTemplate("Ced_CsRfq::quotes/addtoquote.phtml");
        }
    }
    
    /**
     * @return int
     */
    public function getVendorId() {
        if($vendorId = $this->vproducts->getVendorIdByProduct($this->getProductId())) {
            return $vendorId;
        }
        return '0';
    }
    
    
    public function setTemplateProductWise($template){
        return $this->getLayout()->createBlock('Ced\CsRfq\Block\Quotes\Templates')->setTemplate($template);
    }
    
}
