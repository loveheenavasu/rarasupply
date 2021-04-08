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
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsRma\Block\Vrma;

class Rma extends \Magento\Backend\Block\Widget\Container
{
	protected $_template = 'Ced_CsRma::vrma/rma.phtml';
	
    public function __construct(
    		\Magento\Backend\Block\Widget\Context $context,
    		array $data = []
    ) {
    	parent::__construct($context, $data);
    }
    
    public function _construct()
    {
    	
    	parent::_construct();
    	//$this->setData('area','adminhtml');
    }
    
   
    protected function _prepareLayout()
    {
       
	   $this->setChild(
	   		'grid',
	   		$this->getLayout()->createBlock('Ced\CsRma\Block\Vrma\Grid', 'ced.csrma.vendor.rma.grid')
	   );
	        return parent::_prepareLayout();
    }

 

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
    
        return $this->getChildHtml('grid');
    }

   
}
