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
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Advertisement\Block\Adminhtml\Plan;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\Product $product,
        \Ced\Advertisement\Model\Source\Position\PositionIdentifier $identifier,
        \Ced\Advertisement\Model\Source\Plan\Status $status,
        array $data = []
    ){
        $this->_productCollection = $product;
        $this->_status = $status;
        $this->_position = $identifier;
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('plan');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('plan_filter');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
		$collection = $this->_productCollection->getCollection()
                                                ->addAttributeToSelect(['is_plan','duration','price','position_identifier','status','is_plan','name'])
                                                ->addAttributeToFilter('is_plan',1);
        //print_r($collection->getData());die;
		
		$this->setCollection($collection);
        return  parent::_prepareCollection();
    }

    /*protected function _addColumnFilterToCollection($column)
    {
        return parent::_addColumnFilterToCollection($column);
    }*/

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
		$this->addColumn('name', [
				'header'    => __('Plan Name'),
				'align'     =>'centre',
				'index'     => 'name',
				'type'	    => 'text'
			]
		);
		
        $this->addColumn('price', [
                'header'    => __('Price'),
                'align'     =>'centre',
                'index'     => 'price',
                'type'      => 'text'
            ]
        );
        
        $this->addColumn('duration', [
                'header'    => __('Duration'),
                'align'     =>'centre',
                'index'     => 'duration',
                'type'      => 'text'
            ]
        );
        
		$this->addColumn('position_identifier', [
				'header'        => __('Position Identifier'),
				'align'         => 'centre',
				'type'          => 'options',
				'index'         => 'position_identifier',
                'options'       => $this->_position->toOptionArray(),
			]
		);
        
       /* $this->addColumn('status', [
                'header'        => __('Status'),
                'align'         => 'centre',
                'index'         => 'status',
                'sortable'      => false,
                'type'          => 'options',
                
            ]
        );*/
        $this->addColumn('status', [
                'header'        => __('Status'),
                'align'         => 'centre',
                'index'         => 'status',
                'sortable'      => false,
                'type'          => 'options',
                'options'        => $this->_status->toOptionArray(),
                'renderer' => 'Ced\Advertisement\Block\Adminhtml\Plan\Renderer\Status'
            ]
        );

        /*$block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }*/
 
        return parent::_prepareColumns();
    }
 
     protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('id');
    
        
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete Plan(s)'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
 
        //$statuses = $this->_status->toOptionArray();
        
        $this->getMassactionBlock()->addItem('status',
                [
                'label'=> __('Change Status'),
                'url'  => $this->getUrl('*/*/massStatus/', ['_current'=>true]),
                'additional' => [
                        'visibility' => [
                                'name' => 'status',
                                'type' => 'select',
                                'class' => 'required-entry',
                                'label' => ('Status'),
                                'default'=>'-1',
                                'values' => [['value'=>1,'label'=>__('Enable')],['value'=>2,'label'=>__('Disable')]],
                        ]
                    ]
                ]
        );
                
        return $this;
    }
   
 
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['_current' => true,'id' => $row->getId()]);
    }

}
