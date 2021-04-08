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

namespace Ced\CsCmsPage\Block;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Page
 * @package Ced\CsCmsPage\Block
 */
class Page extends \Magento\Framework\View\Element\AbstractBlock implements
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_page;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * Page constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Ced\CsCmsPage\Model\Cmspage $page
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Ced\CsCmsPage\Model\Cmspage $page,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Page\Config $pageConfig,
        array $data = []
    )
    {

        parent::__construct($context, $data);
        // used singleton (instead factory) because there exist dependencies on \Magento\Cms\Helper\Page
        $this->_page = $page;
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $storeManager;
        $this->pageConfig = $pageConfig;

    }

    /**
     * Retrieve Page instance
     *
     * @return \Magento\Cms\Model\Page
     */
    public function getPage()
    {
        $pageId = $this->getRequest()->getParam('page_id', $this->getRequest()->getParam('id', false));

        if (!$this->hasData('page')) {
            if ($pageId) {
                /** @var \Magento\Cms\Model\Page $page */

                $page = $this->_page->load($pageId);
                $page->setStoreId($this->_storeManager->getStore()->getId())->load($pageId, 'identifier');
            } else {
                $page = $this->_page;
            }
            $this->setData('page', $page);
        }
        return $this->getData('page');
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {

        $page = $this->getPage();
        $this->_addBreadcrumbs($page);
        $this->pageConfig->addBodyClass('cscmspage-' . $page->getIdentifier());
        $this->pageConfig->getTitle()->set($page->getTitle());
        $this->pageConfig->setKeywords($page->getMetaKeywords());
        $this->pageConfig->setPageLayout($page->getPageLayout());
        $this->pageConfig->setDescription($page->getMetaDescription());

        $root = $this->getLayout()->getBlock('root');
        if ($root) {
            $root->addBodyClass('cms-' . $page->getIdentifier());
        }

        $head = $this->getLayout()->getBlock('head');
        if ($head) {
            $head->setTitle($page->getTitle());
            $head->setKeywords($page->getMetaKeywords());
            $head->setDescription($page->getMetaDescription());
        }

        return parent::_prepareLayout();
    }

    /**
     * Prepare breadcrumbs
     *
     * @param \Magento\Cms\Model\Page $page
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addBreadcrumbs(\Ced\CsCmsPage\Model\Cmspage $page)
    {

        if ($this->_scopeConfig->getValue('web/default/show_cms_breadcrumbs', ScopeInterface::SCOPE_STORE)
            && ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))
            && $page->getIdentifier() !== $this->_scopeConfig->getValue(
                'web/default/cms_home_page',
                ScopeInterface::SCOPE_STORE
            )
            && $page->getIdentifier() !== $this->_scopeConfig->getValue(
                'web/default/cms_no_route',
                ScopeInterface::SCOPE_STORE
            )
        ) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbsBlock->addCrumb('cscmspage_page', ['label' => $page->getTitle(), 'title' => $page->getTitle()]);
        }
    }

    /**
     * Prepare HTML content
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = $this->_filterProvider->getPageFilter()->filter($this->getPage()->getContent());
        return $html;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Magento\Cms\Model\Page::CACHE_TAG . '_' . $this->getPage()->getId()];
    }
}
