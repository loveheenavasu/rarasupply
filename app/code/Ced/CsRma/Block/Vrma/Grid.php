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
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRma\Block\Vrma;

use Magento\Customer\Model\Session;

/**
 * Class Grid
 * @package Ced\CsRma\Block\Vrma
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Ced\CsRma\Model\ResourceModel\Request\CollectionFactory
     */
    protected $requestcollectionFactory;

    /**
     * Grid constructor.
     * @param \Ced\CsRma\Model\ResourceModel\Request\CollectionFactory $requestcollectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Ced\CsRma\Model\ResourceModel\Request\CollectionFactory $requestcollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        Session $customerSession,
        array $data = []
    )
    {
        $this->session = $customerSession;
        $this->requestcollectionFactory = $requestcollectionFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area', 'adminhtml');
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('rmalistinggrid');
        $this->setDefaultSort('rma_request_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setData('area', 'adminhtml');
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $VendorId = $this->session->getVendorId();
        $collection = $this->requestcollectionFactory->create()
            ->addFieldToFilter('vendor_id', $VendorId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @param _prepareColumns
     */
    protected function _prepareColumns()
    {
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
            'order_id',
            [
                'header' => __('Order Id#'),
                'index' => 'order_id',
            ]
        );
        $this->addColumn(
            'rma_id',
            [
                'header' => __('RMA Id'),
                'index' => 'rma_id',
            ]
        );
        $this->addColumn(
            'ustomer_name',
            [
                'header' => __('Customer Name'),
                'index' => 'customer_name',
            ]
        );
        $this->addColumn(
            'customer_email',
            [
                'header' => __('Customer Email'),
                'index' => 'customer_email',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
            ]
        );

        $this->addColumn(
            'resolution_requested',
            [
                'header' => __('Resolution Requested'),
                'index' => 'resolution_requested',
            ]
        );

        $this->addColumn(
            'updated_at',
            [
                'header' => __('Updated At'),
                'type' => 'datetime',
                'index' => 'updated_at',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('rma_id' => $row->getRmaRequestId()));
    }
}
