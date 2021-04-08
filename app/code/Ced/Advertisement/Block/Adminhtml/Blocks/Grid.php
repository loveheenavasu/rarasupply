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

namespace Ced\Advertisement\Block\Adminhtml\Blocks;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\Advertisement\Model\Blocks $blocks,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ){
        $this->resource = $resource;
        $this->_blocks = $blocks;
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('advertisement_blocks');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('advertisement_blocks_filter');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
		$collection = $this->_blocks->getCollection();
        $customer_grid_flat_table = $this->resource->getTableName('customer_grid_flat');
        $collection->getSelect()->joinLeft($customer_grid_flat_table, 'main_table.customer_id='.$customer_grid_flat_table.'.entity_id', ['name','email']);
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
        $this->addColumn('name', [
                'header'    => __('Customer Name'),
                'align'     =>'centre',
                'index'     => 'name',
                'type'      => 'text'
            ]
        );

        $this->addColumn('email', [
                'header'    => __('Customer Email'),
                'align'     =>'centre',
                'index'     => 'email',
                'type'      => 'text'
            ]
        );

		$this->addColumn('title', [
				'header'    => __('Block Title'),
				'align'     =>'centre',
				'index'     => 'title',
				'type'	    => 'text'
			]
		);
		
        $this->addColumn('url', [
                'header'    => __('Block Url'),
                'align'     =>'centre',
                'index'     => 'url',
                'type'      => 'text'
            ]
        );
        
        $this->addColumn('image', [
                'header'    => __('Block Image'),
                'align'     =>'centre',
                'index'     => 'image',
                'type'      => 'text'
            ]
        );

        $this->addColumn('status', [
                'header'        => __('Status'),
                'align'         => 'centre',
                'index'         => 'status',
                'filter'        => false,
                'type'          => 'text',
                'sortable'      => false,
                'renderer' => 'Ced\Advertisement\Block\Adminhtml\Blocks\Renderer\Status'
            ]
        );
     
        
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('id');
        
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
