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
 * @package     Ced_CsCmsPage
 * @author   CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCmsPage\Block\Block;

use Magento\Customer\Model\Session;

/**
 * Class Grid
 * @package Ced\CsCmsPage\Block\Block
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * Massaction block name
     *
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/grid/extended.phtml';

    /**
     * @var string
     */
    protected $_massactionBlockName = 'Ced\CsCmsPage\Block\Widget\Grid\Massaction';

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_cmsPage;

    /**
     * @var \Ced\CsCmsPage\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * Grid constructor.
     * @param \Magento\Cms\Model\Page $cmsPage
     * @param Session $customerSession
     * @param \Ced\CsCmsPage\Model\BlockFactory $blockFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Page $cmsPage,
        Session $customerSession,
        \Ced\CsCmsPage\Model\BlockFactory $blockFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    )
    {
        $this->_cmsPage = $cmsPage;
        $this->session = $customerSession;
        $this->blockFactory = $blockFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;

        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('cmsPageGrid');
        $this->setDefaultSort('page_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setData('area', 'adminhtml');
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended|void
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('block_id');
        $this->getMassactionBlock()->setFormFieldName('block_id');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/delete'),
                'confirm' => __('Are you sure?')
            ]
        );
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {

        $VendorId = $this->session->getVendorId();
        $collection = $this->blockFactory->create()->getCollection();
        $main_table = $this->csmarketplaceHelper->getTableKey('main_table');
        $vendor_id = $this->csmarketplaceHelper->getTableKey('vendor_id');

        $collection = $collection->addFieldToFilter("$main_table.$vendor_id", array('eq' => $VendorId));
        $collection->setFirstStoreFlag(true);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();

        $this->addColumn('block_id',
            [
                'header' => ('Id'),
                'align' => 'left',
                'index' => 'block_id',
            ]
        );

        $this->addColumn('title',
            [
                'header' => ('Title'),
                'align' => 'left',
                'index' => 'title',
            ]
        );

        $this->addColumn('identifier',
            [
                'header' => ('URL Key'),
                'align' => 'left',
                'index' => 'identifier'
            ]
        );

        $this->addColumn('is_active', [
            'header' => ('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => $this->_cmsPage->getAvailableStatuses()
        ]);

        $this->addColumn('approve', [
                'header' => __('Approve'),
                'align' => 'left',
                'index' => 'is_approve',
                'filter' => false,
                'type' => 'text',
                'renderer' => 'Ced\CsCmsPage\Block\Grid\Renderer\Approved',
            ]
        );

        $this->addColumn(
            'creation_time',
            [
                'header' => __('Created'),
                'index' => 'creation_time',
                'type' => 'datetime',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );

        $this->addColumn('update_time', [
            'header' => ('Last Modified'),
            'index' => 'update_time',
            'type' => 'datetime',
        ]);


        $this->addColumn('action',
            [
                'header' => __('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('Edit'),
                        'url' => array(
                            'base' => '*/*/edit',
                            'params' => array('store' => $this->getRequest()->getParam('block_id'))
                        ),
                        'field' => 'block_id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
            ]);

        return parent::_prepareColumns();
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
        return $this->getUrl('*/*/edit', array('block_id' => $row->getBlockId()));
    }
}
