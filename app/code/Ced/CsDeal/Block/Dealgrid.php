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

use Magento\Customer\Model\Session;
use Ced\CsDeal\Model;

/**
 * Class Dealgrid
 * @package Ced\CsDeal\Block
 */
class Dealgrid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Model\ResourceModel\Deal\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Model\StatusFactory
     */
    protected $statusFactory;

    /**
     * @var Model\DealFactory
     */
    protected $dealFactory;

    /**
     * Dealgrid constructor.
     * @param Model\ResourceModel\Deal\CollectionFactory $collectionFactory
     * @param Model\StatusFactory $statusFactory
     * @param Model\DealFactory $dealFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Model\ResourceModel\Deal\CollectionFactory $collectionFactory,
        Model\StatusFactory $statusFactory,
        Model\DealFactory $dealFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        Session $customerSession,
        array $data = []
    )
    {
        $this->session = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->statusFactory = $statusFactory;
        $this->dealFactory = $dealFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('dealgrid');
        $this->setDefaultSort('post_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $vendorId = $this->getVendorId();
        $collection = $this->collectionFactory->create()->addFieldToFilter('vendor_id', $vendorId);
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('deal_id');
        $this->getMassactionBlock()->setTemplate('Ced_CsDeal::product/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('deal_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => __('Delete'),
            'url' => $this->getUrl('*/*/massDelete', array(
                'confirm' => __('Are you sure?')
            ))
        ));
        $this->getMassactionBlock()->addItem('enable', array(
            'label' => __('Enable'),
            'url' => $this->getUrl('*/*/massEnable', array(
                'confirm' => __('Are you sure?')
            ))
        ));
        $this->getMassactionBlock()->addItem('disable', array(
            'label' => __('Disable'),
            'url' => $this->getUrl('*/*/massDisable', array(
                'confirm' => __('Are you sure?')
            ))
        ));
        return $this;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $store = $this->_getStore();
        $this->addColumn('deal_id',
            array(
                'header' => __('Deal Id'),
                'width' => '5px',
                'type' => 'text',
                'align' => 'left',
                'index' => 'deal_id',
            ));

        $this->addColumn('product_name',
            array(
                'header' => __('Product Name'),
                'width' => '150px',
                'type' => 'text',
                'align' => 'left',
                'index' => 'product_name',
            ));

        $this->addColumn('deal_price',
            array(
                'header' => __('Deal Price'),
                'type' => 'currency',
                'width' => '5px',
                'index' => 'deal_price',
            ));

        $this->addColumn('end_date',
            array(
                'header' => __('Deal End'),
                'type' => 'date',
                'index' => 'end_date',
            ));

        $this->addColumn('status',
            array(
                'header' => __('Deal Status'),
                'width' => '5px',
                'type' => 'options',
                'align' => 'left',
                'index' => 'status',
                'options' => $this->statusFactory->create()->toOptionArray()

            ));

        $this->addColumn('admin_status',
            array(
                'header' => __('Admin Status'),
                'width' => '5px',
                'type' => 'options',
                'align' => 'left',
                'index' => 'admin_status',
                'options' => $this->dealFactory->create()->getMassActionArray(),
                'renderer' => 'Ced\CsDeal\Block\Edit\Tab\Renderer\AdminStatus',
            ));

        $this->addColumn('edit_deal',
            array(
                'header' => __('Edit'),
                'width' => '70px',
                'index' => 'edit_deal',
                'sortable' => false,
                'filter' => false,
                'renderer' => 'Ced\CsDeal\Block\Edit\Tab\Renderer\Deal',
            ));

        return parent::_prepareColumns();
    }

    /**
     * @return mixed
     */
    public function getVendorId()
    {
        return $this->session->getVendorId();
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
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/listdeal');
    }

}
