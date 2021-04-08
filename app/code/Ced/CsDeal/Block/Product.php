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

class Product extends \Magento\Backend\Block\Widget\Container
{    
	protected $_template = 'csdeal/product.phtml';
    protected $_typeFactory;
    protected $_productFactory;
	protected function _construct()
	{
		$this->_controller = 'deal';
		$this->_blockGroup = 'Ced_CsDeal';
		$this->_headerText = __('Products');

		
	    	parent::_construct();
		$this->buttonList->update('save', 'label', __('Save Grid'));
        /*$this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        );*/
 
        $this->buttonList->update('delete', 'label', __('Delete Grid'));
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

		$newurl = $this->getUrl('csdeal/deal/listi');
    	$this->addButton('view_deals', array(
            'label'   => __('View Deals'),
            'onclick' => "setLocation('{$newurl}')",
            'class'   => 'action-default primary add',
            'area' => 'adminhtml'
        ));

	   $this->setChild(
				'grid',
				$this->getLayout()->createBlock('Ced\CsDeal\Block\Grid', 'ced.csdeal.vendor.product.grid')
		); 	
		return parent::_prepareLayout(); 
		
	}
	

   
    public function getGridHtml()
    {
    	return $this->getChildHtml('grid');
    }

   
 
}
