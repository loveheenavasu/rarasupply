<?php

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
 * @package     Ced_CsDeal
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Block;

use Magento\Store\Model\Store;
use Magento\Customer\Model\Session;

/**
 * Class Grid
 * @package Ced\CsDeal\Block
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Ced\CsMarketplace\Model\Vproducts
     */
    protected $_vproduct;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Ced\CsDeal\Model\DealFactory
     */
    protected $dealFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Grid constructor.
     * @param \Ced\CsDeal\Model\DealFactory $dealFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Ced\CsMarketplace\Model\Vproducts $vproduct
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Ced\CsDeal\Model\DealFactory $dealFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Ced\CsMarketplace\Model\Vproducts $vproduct,
        Session $customerSession,
        array $data = []
    )
    {
        $this->_productFactory = $productFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        $this->_vproduct = $vproduct;
        $this->session = $customerSession;
        $this->dealFactory = $dealFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendorproductdealGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('entity_id');
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {

        $vendorId = $this->getVendorId();
        $vproducts = array();
        $vproducts = $this->_vproduct->getVendorProductIds();
        $dealproducts = $this->dealFactory->create()->getVendorDealProductIds($vendorId);
        $store = $this->_getStore();
        $collection = $this->_productFactory->create()->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->setStore($store)
            ->addAttributeToFilter('type_id', ['neq' => 'configurable'])
            ->addFieldToFilter('entity_id', array('in' => $vproducts));
        if (count($dealproducts))
            $collection->addAttributeToFilter('entity_id', array('nin' => $dealproducts));

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }
        if ($store->getId()) {
            $collection->setStoreId($store->getId());
            $collection->addStoreFilter($store);
            $collection->joinAttribute(
                'name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                Store::DEFAULT_STORE_ID
            );
            $collection->joinAttribute(
                'custom_name',
                'catalog_product/name',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'status',
                'catalog_product/status',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute(
                'visibility',
                'catalog_product/visibility',
                'entity_id',
                null,
                'inner',
                $store->getId()
            );
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        $collection->joinField('check_status', 'ced_csmarketplace_vendor_products', 'check_status', 'product_id=entity_id', null, 'left');


        $this->setCollection($collection);
        $this->getCollection()->addWebsiteNamesToResult();
        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', ['header' => __('Product Id'), 'index' => 'entity_id']);

        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn(
            'type_id',
            [
                'header' => __('Type'),
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->productTypes(),
            ]
        );

        $this->addColumn('price', ['header' => __('Price'), 'index' => 'price']);

        $this->addColumn(
            'qty',
            [
                'header' => __('Qty'),
                'index' => 'qty',
            ]
        );


        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_status->getOptionArray()
            ]
        );


        $this->addColumn('create_deal',
            array(
                'header' => __('Create'),
                'width' => '70px',
                'index' => 'create_deal',
                'sortable' => false,
                'filter' => false,
                'renderer' => 'Ced\CsDeal\Block\Grid\Renderer\Deal',
            ));

        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    protected function productTypes()
    {
        $options = $this->_type->getOptionArray();
        unset($options['configurable']);
        return $options;
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                );
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
    protected function _productStatusFilter($collection, $column)
    {
        if (!strlen($column->getFilter()->getValue())) {
            return $this;
        }
        if ($column->getFilter()->getValue() == \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS . \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            $this->getCollection()
                ->addAttributeToFilter('check_status', array('eq' => \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS))
                ->addAttributeToFilter('status', array('eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED));
        } else if ($column->getFilter()->getValue() == \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS . \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
            $this->getCollection()
                ->addAttributeToFilter('check_status', array('eq' => \Ced\CsMarketplace\Model\Vproducts::APPROVED_STATUS))
                ->addAttributeToFilter('status', array('eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED));
        } else
            $this->getCollection()->addAttributeToFilter('check_status', array('eq' => $column->getFilter()->getValue()));
        return $this;
    }

    /**
     * Filter store condition
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Magento\Framework\DataObject $column
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * @return string
     */
    protected function _getUrlModelClass()
    {
        return 'core/url';
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Prepare grid filter buttons
     *
     * @return void
     */
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
        $this->setChild(
            'reset_filter_button1',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('View Deals'),
                    'onclick' => '',
                    'class' => 'action-reset action-primary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-view-deals'])
        );
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->session->getVendorId();
    }

}