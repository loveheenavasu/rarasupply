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
 * @package     Ced_CsDeal
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Block;

class Deal extends \Magento\Backend\Block\Widget\Container
{    
	protected $_template = 'csdeal/product.phtml';
    protected $_typeFactory;
    protected $_productFactory;

	protected function _construct()
	{
		$this->_controller = 'deal';
		$this->_blockGroup = 'Ced_CsDeal';
		$this->_headerText = __('Vendor Deals');
	
		parent::_construct();
		//$this->buttonList->update('add', 'label', __('Add New Product'));
	}
	
	public function __construct(
		\Magento\Backend\Block\Widget\Context $context,
		\Magento\Catalog\Model\Product\TypeFactory $typeFactory,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		array $data = []
	) {
		$this->_productFactory = $productFactory;
        $this->_typeFactory = $typeFactory;
		
		parent::__construct($context, $data);
	}
	
	protected function _prepareLayout()
	{
		$newurl = $this->getUrl('csdeal/deal/create');
		$this->addButton('create_deals', array(
				'label'   => __('Create Deal'),
				'onclick' => "setLocation('{$newurl}')",
				'class'   => 'action-default primary add',
				'area' => 'adminhtml'
						));
		$this->setChild(
				'grid',
				$this->getLayout()->createBlock('Ced\CsDeal\Block\Dealgrid', 'ced.csproduct.vendor.deal.grid')
		); 	
		
		return parent::_prepareLayout(); 
		
	}
	
    
   	
    public function getGridHtml()
    {
    	return $this->getChildHtml('grid');
    }

    
}
