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
namespace Ced\CsRma\Block\Adminhtml\AllRma\Order;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended  
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollection;

  	 /**
  	 *  @param Magento\Backend\Block\Template\Context
  	 *  @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory 
     *  @param Magento\Backend\Helper\Data
  	 */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->orderCollection = $orderCollection;
        parent::__construct($context,$backendHelper,$data);
    }
	
    /**
     *@param construct
     */
    protected function _construct()
    {
    	$this->setId('grid_id');
        $this->setRowClickCallback('review.gridRowClick');
        $this->setUseAjax(true);
    }
 	
    /**
     *@param prepareCollection
     */
    protected function _prepareCollection()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $model = $this->orderCollection->create();
        if($order_id){
            $collection = $model->addFieldToFilter('entity_id', $order_id);
        } else {
            $collection = $model;
        }      
      $this->setCollection($collection);
      return parent::_prepareCollection();

    }
    
    /**
     *@param _prepareColumns
     */
    protected function _prepareColumns()
    {
      
    	$this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'index' => 'increment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

      /**
         * Check is single store mode
         */

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                ['header' => __('Purchased Point'), 'index' => 'store_id', 'type' => 'store', 'store_view' => true]
            );
        }

      $this->addColumn(
            'created_at',
            [
                'header' => __('Purchased Date'),
                'type' => 'datetime',
                'index' => 'created_at',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );

      $this->addColumn(
            'base_grand_total',
            [
                'header' => __('Grnad Total (Base)'),
                'index' => 'base_grand_total',
            ]
        );

      $this->addColumn(
            'grand_total',
            [
                'header' => __('Grnad Total (Purchased)'),
                'index' => 'grand_total',
            ]
        );
      $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
            ]
        ); 
    }
	
    /**
     * getGridUrl
     *
     * @return $this
     */
    public function getGridUrl()
    {
        return $this->getUrl('csrma/allrma/postGrid', ['_current' => true]);
    }
    
    /**
     * getRowUrl
     *
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('csrma/allrma/jsonInfo', ['id' => $row->getId()]);
    } 
    /**
     * Prepare mass action
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }
}
