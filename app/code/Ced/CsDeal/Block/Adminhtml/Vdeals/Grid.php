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

namespace Ced\CsDeal\Block\Adminhtml\Vdeals;

/**
 * Class Grid
 * @package Ced\CsDeal\Block\Adminhtml\Vdeals
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Ced\CsDeal\Model\DealFactory
     */
    protected $_messagingFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * Grid constructor.
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Ced\CsDeal\Model\DealFactory $messagingFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\CsDeal\Model\DealFactory $messagingFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    )
    {
        $this->_messagingFactory = $messagingFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('gridGrid');
        $this->setDefaultSort('chat_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('grid_record');
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_messagingFactory->create()->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
        $this->addColumn('deal_id',
            array(
                'header' => __('Deal ID'),
                'width' => '10px',
                'type' => 'number',
                'align' => 'left',
                'index' => 'deal_id',
            ));
        $this->addColumn('product_id',
            array(
                'header' => __('Product ID'),
                'width' => '10px',
                'type' => 'number',
                'align' => 'left',
                'index' => 'product_id',
            ));
        $this->addColumn('product_name',
            array(
                'header' => __('Product Name'),
                'width' => '200px',
                'align' => 'left',
                'index' => 'product_name',
            ));
        $this->addColumn('vendor_id',
            array(
                'header' => __('Vendor Id'),
                'align' => 'left',
                'width' => '10px',
                'index' => 'vendor_id',
            ));
        $this->addColumn('vendor_id',
            array(
                'header' => __('Vendor Name'),
                'align' => 'left',
                'width' => '300px',
                'index' => 'vendor_id',
                'renderer' => 'Ced\CsDeal\Block\Adminhtml\Vdeals\Renderer\Vendorname',
                'filter_condition_callback' => array($this, '_vendornameFilter'),
            ));
        $this->addColumn('status',
            array(
                'header' => __('Status'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'status',
            ));
        $store = $this->_getStore();
        $this->addColumn('product_price',
            array(
                'header' => __('Product Price'),
                'width' => '80px',
                'type' => 'currency',
                'align' => 'left',
                'index' => 'product_id',
                'renderer' => 'Ced\CsDeal\Block\Adminhtml\Vdeals\Renderer\Productprice',
                'filter_condition_callback' => array($this, '_productPriceFilter'),
            ));
        $this->addColumn('deal_price', array(
            'header' => __('Deal Price'),
            'width' => '80px',
            'index' => 'deal_price',
            'type' => 'currency',
        ));
        $this->addColumn('action',
            array(
                'header' => __('Action'),
                'type' => 'text',
                'width' => '120px',
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Ced\CsDeal\Block\Adminhtml\Vdeals\Renderer\Action',
                'index' => 'action',
            ));
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
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
     * @param $collection
     * @param $column
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _vendornameFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $vendorIds = $this->vendorFactory->create()->getCollection()
            ->addAttributeToFilter('name', array('like' => '%' . $column->getFilter()->getValue() . '%'))
            ->getAllIds();

        if (count($vendorIds) > 0)
            $this->getCollection()->addFieldToFilter('vendor_id', array('in', $vendorIds));
        else {
            $this->getCollection()->addFieldToFilter('vendor_id');
        }
        return $this;
    }

    /**
     * @param $collection
     * @param $column
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _productPriceFilter($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');

        if (isset($value['from']) && isset($value['to'])) {
            $productCollection->addAttributeToFilter('price', ['from' => $value['from'], 'to' => $value['to']]);
        } elseif (isset($value['from'])) {
            $productCollection->addAttributeToFilter('price', ['from' => $value['from']]);
        } elseif (isset($value['to'])) {
            $productCollection->addAttributeToFilter('price', ['from' => 0, 'to' => $value['to']]);
        }
        $pIds = $productCollection->getColumnValues('entity_id');
        if (count($pIds) > 0) {
            $this->getCollection()->addFieldToFilter('product_id', array('in', $pIds));
        } else {
            $this->getCollection()->addFieldToFilter('product_id');
        }
        return $this;
    }
}
