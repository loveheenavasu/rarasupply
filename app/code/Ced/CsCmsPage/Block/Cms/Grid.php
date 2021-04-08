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

namespace Ced\CsCmsPage\Block\Cms;

use Magento\Customer\Model\Session;

/**
 * Class Grid
 * @package Ced\CsCmsPage\Block\Cms
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_cmsPage;

    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * @var \Ced\CsCmsPage\Model\CmspageFactory
     */
    protected $cmspageFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var string
     */
    protected $_massactionBlockName = 'Ced\CsCmsPage\Block\Widget\Grid\Massaction';

    /**
     * Grid constructor.
     * @param \Magento\Cms\Model\Page $cmsPage
     * @param Session $customerSession
     * @param \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Page $cmsPage,
        Session $customerSession,
        \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    )
    {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->_cmsPage = $cmsPage;
        $this->session = $customerSession;
        $this->cmspageFactory = $cmspageFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;

        parent::__construct($context, $backendHelper, $data);

    }

    /**
     *
     */
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
        $this->setMassactionIdField('page_id');
        $this->getMassactionBlock()->setFormFieldName('page_id');

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

        $collection = $this->cmspageFactory->create()->getCollection();

        $main_table = $this->csmarketplaceHelper->getTableKey('main_table');
        $vendor_id = $this->csmarketplaceHelper->getTableKey('vendor_id');

        $collection = $collection->addFieldToFilter("$main_table.$vendor_id", array('eq' => $VendorId));
        $collection->setFirstStoreFlag(false);

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

        $this->addColumn('page_id',
            [
                'header' => ('Id'),
                'align' => 'left',
                'index' => 'page_id',
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

        $this->addColumn('root_template',
            [
                'header' => ('Layout'),
                'index' => 'page_layout',
                'type' => 'options',
                'options' => $this->pageLayoutBuilder->getPageLayoutsConfig()->getOptions()
            ]);

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
                'renderer' => 'Ced\CsCmsPage\Block\Grid\Renderer\Approve',
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

        $this->addColumn(
            'page_actions',
            [
                'header' => __('Action'),
                'sortable' => false,
                'filter' => false,
                'renderer' => 'Ced\CsCmsPage\Block\Adminhtml\Cmspage\Grid\Renderer\Action',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'


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
                            'params' => array('store' => $this->getRequest()->getParam('page_id'))
                        ),
                        'field' => 'page_id'
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
        return $this->getUrl('*/*/edit', array('page_id' => $row->getPageId()));
    }
}
