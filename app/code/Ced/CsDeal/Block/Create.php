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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Block;

use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Create
 * @package Ced\CsDeal\Block
 */
class Create extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type
     */
    protected $_type;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_filtercollection;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $productFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Ced\CsDeal\Model\StatusFactory
     */
    public $statusFactory;

    /**
     * @var \Ced\CsDeal\Model\DealFactory
     */
    public $dealFactory;

    /**
     * Create constructor.
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Ced\CsDeal\Model\StatusFactory $statusFactory
     * @param \Ced\CsDeal\Model\DealFactory $dealFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param \Ced\CsMarketplace\Model\Session $customerSession
     * @param UrlFactory $urlFactory
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Ced\CsDeal\Model\StatusFactory $statusFactory,
        \Ced\CsDeal\Model\DealFactory $dealFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        \Ced\CsMarketplace\Model\Session $customerSession,
        UrlFactory $urlFactory
    )
    {
        $this->vproductsFactory = $vproductsFactory;
        $this->_type = $type;
        $this->storeManager = $context->getStoreManager();
        $this->productFactory = $productFactory;
        $this->storeFactory = $storeFactory;
        $this->statusFactory = $statusFactory;
        $this->dealFactory = $dealFactory;

        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

        $vendorId = $this->getVendorId();
        $collection = $this->vproductsFactory->create()->getVendorProducts('', $vendorId, 0);

        if (count($collection) > 0) {
            $products = array();
            $statusarray = array();
            foreach ($collection as $data) {
                array_push($products, $data->getProductId());
                $statusarray[$data->getProductId()] = $data->getCheckStatus();
            }
            $currentStore = $this->storeManager->getStore(null)->getId();
            $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            $productcollection = $this->productFactory->create()->getCollection();

            $storeId = 0;
            if ($this->getRequest()->getParam('store')) {
                $websiteId = $this->storeFactory->create()->load($this->getRequest()->getParam('store'))->getWebsiteId();
                if ($websiteId) {
                    if (in_array($websiteId, $this->vproductsFactory->create()->getAllowedWebsiteIds())) {
                        $storeId = $this->getRequest()->getParam('store');
                    }
                }
            }

            $productcollection->addAttributeToSelect('*')->addAttributeToFilter('entity_id', array('in' => $products))->addAttributeToSort('entity_id', 'DESC');

            if ($storeId) {
                $productcollection->addStoreFilter($storeId);
                $productcollection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $storeId);
                $productcollection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $storeId);
                $productcollection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $storeId);
                $productcollection->joinAttribute('thumbnail', 'catalog_product/thumbnail', 'entity_id', null, 'left', $storeId);
            }

            if (true) {
                $productcollection->joinField('qty',
                    'cataloginventory_stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left');
            }
            $productcollection->joinField('check_status', 'ced_csmarketplace_vendor_products', 'check_status', 'product_id=entity_id', null, 'left');

            $params = $this->session->getData('product_filter');
            if (isset($params) && is_array($params) && count($params) > 0) {
                foreach ($params as $field => $value) {
                    if ($field == 'store' || $field == 'store_switcher' || $field == "__SID")
                        continue;
                    if (is_array($value)) {
                        if (isset($value['from']) && urldecode($value['from']) != "") {
                            $from = urldecode($value['from']);
                            $productcollection->addAttributeToFilter($field, array('gteq' => $from));
                        }
                        if (isset($value['to']) && urldecode($value['to']) != "") {
                            $to = urldecode($value['to']);
                            $productcollection->addAttributeToFilter($field, array('lteq' => $to));
                        }
                    } else if (urldecode($value) != "") {
                        $productcollection->addAttributeToFilter($field, array("like" => '%' . urldecode($value) . '%'));
                    }
                }
            }

            $this->storeManager->setCurrentStore($currentStore);
            $productcollection->setStoreId($storeId);
            if ($productcollection->getSize() > 0) {
                $this->_filtercollection = $productcollection;
                $this->setVproducts($this->_filtercollection);
            }
        }
    }

    /**
     * @return $this|\Ced\CsMarketplace\Block\Vendor\AbstractBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {


        parent::_prepareLayout();
        if ($this->_filtercollection) {
            if ($this->_filtercollection->getSize() > 0) {
                if ($this->getRequest()->getActionName() == 'index') {
                    $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'custom.pager');
                    $pager->setAvailableLimit(array(5 => 5, 10 => 10, 20 => 20, 'all' => 'all'));
                    $pager->setCollection($this->_filtercollection);
                    $this->setChild('pager', $pager);
                }
            }
        }
        return $this;
    }

    /**
     * Get pager HTML
     *
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * get Edit product url
     *
     */
    public function getEditUrl($product)
    {
        return $this->getUrl('*/*/edit', array('_nosid' => true, 'id' => $product->getId(), 'type' => $product->getTypeId(), 'store' => $this->getRequest()->getParam('store', 0)));
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->_type->toOptionArray(false, true);
    }

    /**
     * get Product Type url
     *
     */
    public function getProductTypeUrl()
    {
        return $this->getUrl('*/*/new/', array('_nosid' => true));
    }

    /**
     * get Delete url
     *
     */
    public function getDeleteUrl($product)
    {
        return $this->getUrl('*/*/delete', array('_nosid' => true, 'id' => $product->getId()));
    }

    /**
     * back Link url
     *
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->vproductsFactory->create()->getVendorProductIds();
        if (in_array($id, $ids)) {
            return $id;
        }
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->productFactory->create()->load($id);
        return $model->getName();
    }

}
