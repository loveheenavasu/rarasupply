<?php
namespace Ced\Rewardsystem\Block\Points;

use \Ced\Rewardsystem\Helper\Data;
use \Magento\Framework\Registry;
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
 * @package     Ced_Rewardsystem
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $_gridFactory;
    protected $session;
    protected $storeManager;
    protected $_status;
    protected $rewardsystem_helper;
    protected $dataObject;
    protected $collectionFactory;
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\DataObjectFactory $dataObject,
        Data $rewardsystem_helper,
        Registry $registry,
        array $data = []
    )
    {
        $this->storeManager = $storeManager;
        $this->_gridFactory = $regisuserpointFactory;
        $this->session = $customerSession;
        $this->rewardsystem_helper = $rewardsystem_helper;
        $this->collectionFactory = $collectionFactory;
        $this->dataObject = $dataObject;
        $this->registry = $registry;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
        //$this->_prepareCollection();
    }


    protected function _construct()
    {
        parent::_construct();
        $this->setId('cedrewardGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //  $this->setVarNameFilter('post_filter');


    }

    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    protected function _prepareCollection()
    {
        $customerId = $this->session->getCustomer()->getId();
       
        $collection = $this->_gridFactory->create()->getCollection()->addFieldToFilter('customer_id',$customerId);

        //set the details array in registry
        $registry_data = $this->rewardsystem_helper->getCustomerWisePointSheet($customerId);
        if( empty($registry_data[$customerId]['points_data']) )
            $registry_data[$customerId]['points_data'] = [];
        $this->registry->register('points_data', $registry_data[$customerId]['points_data']);

        if( isset($_GET['test']) ){
            echo '<pre>';print_r($registry_data);die;
        }
        /* $collection_object = $this->rewardsystem_helper->getCustomerWisePointSheet($customerId);

        if( empty($collection_object[$customerId]['points_data']) )
            $collection_object[$customerId]['points_data'] = [];

        $collection = $this->collectionFactory->create();
        foreach( $collection_object[$customerId]['points_data'] as $key => $value ){
            $row_data = $this->dataObject->create()->setData($value);
            $collection->addItem($row_data);
        } */

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $this->addColumn(
            'id',
            [
                'header' => __('ID #'),
                'index' => 'id',
            ]
        );

        $this->addColumn(
            'creating_date',
            [
                'header' => __('Created On'),
                'type' => 'datetime',
                'index' => 'creating_date',

            ]
        );

        $this->addColumn(
            'updated_date',
            [
                'header' => __('Approved On'),
                'type' => 'datetime',
                'index' => 'updated_at',
            ]
        );

        $this->addColumn(
            'order_id',
            [
                'header' => __('Order Id'),
                'index' => 'order_id',
            ]
        );

        $this->addColumn(
            'point',
            [
                'header' => __('Points Earned'),
                'type' => 'text',
                'index' => 'point'
            ]
        );
        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'type' => 'text',
                'index' => 'title',
            ]
        );

        $this->addColumn(
            'point_used',
            [
                'header' => __('Points Used (In Order)'),
                'type' => 'text',
                'index' => 'point_used'
            ]
        );

        /*$this->addColumn(
            'redeem_points',
            [
                'header' => __('Points Redeemed (on Order Cancellation)'),
                'type' => 'text',
                'index' => 'id',
                'filter' => false,
                'renderer' => 'Ced\Rewardsystem\Block\Points\Grid\RedeemPoints'
            ]
        );*/

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'type' => 'text',
                'index' => 'status'
            ]
        );

        $this->addColumn(
            'expiration_date',
            [
                'header' => __('Expire At'),
                'type' => 'date',
                'index' => 'expiration_date',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/rewardgrid', ['_current' => true]);
    }

    protected function _prepareFilterButtons()
    {
        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Reset Filter'),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()',
                    'class' => 'action-reset action-tertiary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-reset'])
        );
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Search'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'action-secondary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-apply'])
        );
    }

}