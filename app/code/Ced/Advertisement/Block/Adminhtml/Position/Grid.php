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

namespace Ced\Advertisement\Block\Adminhtml\Position;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\Advertisement\Model\Positions $positions,
        array $data = []
    ){
        $this->_positionCollection = $positions;
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('positions');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('position_filter');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
		$collection = $this->_positionCollection->getCollection();
		
		$this->setCollection($collection);
        return  parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
		$this->addColumn('position_name', [
				'header'    => __('Position Name'),
				'align'     =>'centre',
				'index'     => 'position_name',
				'type'	  => 'text'
			]
		);
		
		$this->addColumn('identifier', [
				'header'        => __('Identifier'),
				'align'         => 'centre',
				'type'          => 'text',
				'index'         => 'identifier'
			]
		);
        
        $this->addColumn('position_status', [
                'header'        => __('Status'),
                'align'         => 'centre',
                'index'         => 'position_status',
                'filter'        => false,
                'type'          => 'text',
                'sortable'      => false,
                'renderer' => 'Ced\Advertisement\Block\Adminhtml\Position\Renderer\Status'
            ]
        );


        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
 
        return parent::_prepareColumns();
    }
 
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('id');
    
 
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
                                'values' => [['value'=>1,'label'=>__('Approve')],['value'=>0,'label'=>__('Disapprove')]],
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
}
