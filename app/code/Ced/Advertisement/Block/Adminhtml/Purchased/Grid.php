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

namespace Ced\Advertisement\Block\Adminhtml\Purchased;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Ced\Advertisement\Model\Purchased $purchased,
        array $data = []
    ){
        $this->_purchased = $purchased;
        $this->resource = $resource;
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('purchased');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('purchased_filter');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
		$collection = $this->_purchased->getCollection();
		$customer_grid_flat_table = $this->resource->getTableName('customer_grid_flat');
        $collection->getSelect()->joinLeft($customer_grid_flat_table, 'main_table.customer_id='.$customer_grid_flat_table.'.entity_id', ['name','email']);
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
		$this->addColumn('id', [
				'header'    => __('Subscription Id'),
				'align'     =>'centre',
				'index'     => 'id',
				'type'	    => 'text'
			]
		);
		
        $this->addColumn('plan_title', [
                'header'    => __('Plan Title'),
                'align'     =>'centre',
                'index'     => 'plan_title',
                'type'      => 'text'
            ]
        );

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
        
        $this->addColumn('block_title', [
                'header'    => __('Block Title'),
                'align'     =>'centre',
                'index'     => 'block_title',
                'type'      => 'text'
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

        $this->addColumn('status', [
                'header'    => __('Status'),
                'align'     =>'centre',
                'index'     => 'status',
                'type' =>'options',
                'options' => ['0'=>'disable','1'=>'enable'],
            ]
        );
 
        return parent::_prepareColumns();
    }
    
 
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

        protected function _prepareMassaction()
   {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('id');
        
        
        $this->getMassactionBlock()->addItem('status',
            [
                'label' => __('Status'),
                'url' => $this->getUrl('advertisement/purchased/massstatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => [
                            ['value' => '1', 'label' => __('Enable')],
                            ['value' => '0', 'label' => __('Disable')]
                        ],
                    ]
                ]
            ]
        );
        
        

        $this->getMassactionBlock()->addItem(
            'delete',
            [
               // 'label' => __('Delete'),
              //  'url' => $this->getUrl('fruitseller/ambassador/massdelete'),
               // 'confirm' => __('Are you sure?')
            ]
        );
        return $this;
    }

}
