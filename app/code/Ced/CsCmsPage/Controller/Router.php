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

namespace Ced\CsCmsPage\Controller;

/**
 * Class Router
 * @package Ced\CsCmsPage\Controller
 */
class Router extends \Ced\CsMarketplace\Controller\Router
{
    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * @var \Ced\CsCmsPage\Helper\Data
     */
    protected $cmsHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * Router constructor.
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Framework\Module\Manager $manager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Ced\CsCmsPage\Model\CmspageFactory $pageFactory
     * @param \Ced\CsCmsPage\Helper\Data $cmsHelper
     * @param \Ced\CsSeoSuite\Helper\Data $seosuiteHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Framework\Module\Manager $manager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Ced\CsCmsPage\Model\CmspageFactory $pageFactory,
        \Ced\CsCmsPage\Helper\Data $cmsHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
    )
    {
        parent::__construct($csmarketplaceHelper, $manager);

        $this->_coreRegistry = $coreRegistry;
        $this->_eventManager = $eventManager;
        $this->_pageFactory = $pageFactory;
        $this->cmsHelper = $cmsHelper;
        $this->request = $request;
        $this->vendorFactory = $vendorFactory;
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->cmsHelper->isEnabled()) {
            return parent::match($request);
        }
        $identifier1 = trim($request->getPathInfo(), '/');

        $suffix = \Ced\CsMarketplace\Model\Vendor::VENDOR_SHOP_URL_SUFFIX;
        $url_path = 'vendor_shop/';
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        if ($this->manager->isEnabled('Ced_CsSeoSuite') && $om->get('Ced\CsSeoSuite\Helper\Data')->isEnabled()) {
            $url_path = $this->csmarketplaceHelper->getStoreConfig('ced_vseo/general/marketplace_url_key') . '/';
        }

        if (strpos($identifier1, $url_path) !== false && strpos($identifier1, $suffix) !== false) {
            $urls = explode('/', $identifier1);
            $url = explode($suffix, end($urls));


            $redirect = $this->checkhome($url);

            if ($redirect == 1) {
                $urls = explode('/', $identifier1);
                $url = explode($suffix, end($urls));
                $request->setModuleName('csmarketplace')->setControllerName('vshops')->setActionName('view')->setParam('shop_url', $url[0]);
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier1);

                return;

            } else {
                $request->setModuleName('cscmspage')->setControllerName('page')->setActionName('index')->setParam('shop_url', $url[0]);
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier1);
                return;
            }
        }

        $identifier = trim($request->getPathInfo(), '/');
        $pathUrl = $identifier;
        if (strpos($identifier, 'vendorshop/') !== false) {
            $condition = new \Magento\Framework\DataObject(['identifier' => $identifier, 'continue' => true]);
            $this->_eventManager->dispatch(
                'cscmspage_controller_router_match_before',
                ['router' => $this, 'condition' => $condition]
            );
            $identifier = $condition->getIdentifier();

            $page = $this->_pageFactory->create()->getCollection()->addFieldToFilter('identifier', $pathUrl)->getFirstItem();

            $request->setModuleName('cscmspage')->setControllerName('page')->setActionName('view')->setParam('page_id', $page->getPageId());
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
            return;
        }


    }


    /**
     * @param $url
     * @return int|void
     */
    public function checkHome($url)
    {
        if (!$this->cmsHelper->isEnabled()) {
            return;
        }

        $pageId = '';
        if ($vendors = $this->_initVendor($url)) {

            if ($vendors->getSize() > 0 && $vendors->getEntityId() > 0) {
                $CmsPage = $this->_pageFactory->create()->getCollection()
                    ->addFieldToFilter('vendor_id', $vendors->getEntityId())
                    ->addFieldToFilter('is_active', '1')
                    ->addFieldToFilter('is_approve', '1');


                if ($CmsPage->getSize() > 0) {
                    foreach ($CmsPage as $cmspage) {

                        $CmsStores = $this->_pageFactory->create()->getCollection()
                            ->addFieldToFilter('page_id', $cmspage->getPageId())
                            ->addFieldToFilter('is_home', '1');


                        if (count($CmsStores) > 0) {
                            $pageId = $cmspage->getPageId();
                        } else {
                            $CmsStores = $this->_pageFactory->create()->getCollection()
                                ->addFieldToFilter('page_id', $cmspage->getPageId())
                                ->addFieldToFilter('is_home', '1');

                            if (count($CmsStores) > 0) {
                                $pageId = $cmspage->getPageId();
                            }
                        }
                    }
                }
                if ($pageId > 0) {

                    return 2;

                } else {
                    return 1;
                }
            }
        }


    }

    /**
     * @param $url
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _initVendor($url)
    {
        $this->_eventManager->dispatch('csmarketplace_controller_vshops_init_before',
            ['controller_action' => $this]);


        $shopUrl = $url[0];

        if (!strlen($shopUrl)) {
            return false;
        }
        $storeId = (int)$this->request->getParam('store', 0);
        $vendor = $this->vendorFactory->create()
            ->setStoreId($storeId)
            ->loadByAttribute('shop_url', $shopUrl);

        if (!$this->csmarketplaceHelper->canShow($vendor)) {
            return false;
        }
        $this->_coreRegistry->registry('current_vendor', $vendor);

        try {
            $this->_eventManager->dispatch(
                'csmarketplace_controller_vshops_init_after',
                ['vendor' => $vendor,
                    'controller_action' => $this]

            );
        } catch (\Exception $e) {

            return false;
        }

        return $vendor;
    }

}
