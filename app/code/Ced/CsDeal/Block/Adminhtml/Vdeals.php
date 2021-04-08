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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Block\Adminhtml;
 
class Vdeals extends \Magento\Backend\Block\Widget\Container
{
    protected $_template = 'grid/view.phtml';
 
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
    	parent::__construct($context, $data);
    }
 
    protected function _prepareLayout()
    {  
    	$this->setChild(
            'grid',
           $this->getLayout()->createBlock('Ced\CsDeal\Block\Adminhtml\Vdeals\Grid', 'grid.view.grid')
        );
        return parent::_prepareLayout();
    }
 
    protected function _getAddButtonOptions()
    {
 
        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];
 
        return $splitButtonOptions;
    }
 
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'grid/*/new'
        );
    }
 
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}