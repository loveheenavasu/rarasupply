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

namespace Ced\CsCmsPage\Controller\Vblock;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class Edit
 * @package Ced\CsCmsPage\Controller\Vblock
 */
class Edit extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsCmsPage\Helper\Data
     */
    protected $cmsHelper;

    /**
     * @var \Ced\CsCmsPage\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * Edit constructor.
     * @param \Ced\CsCmsPage\Helper\Data $cmsHelper
     * @param \Ced\CsCmsPage\Model\BlockFactory $blockFactory
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Ced\CsCmsPage\Helper\Data $cmsHelper,
        \Ced\CsCmsPage\Model\BlockFactory $blockFactory,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );

        $this->cmsHelper = $cmsHelper;
        $this->blockFactory = $blockFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {

        if (!$this->cmsHelper->isEnabled()) {
            return;
        }
        if (!$this->_getSession()->getVendorId())
            return;


        if ($this->getRequest()->getParam('block_id') > 0) {
            $block_id = $this->getRequest()->getParam('block_id');
            $vendorcmsblock = $this->blockFactory->create()->load($block_id);
            if ($vendorcmsblock && $vendorcmsblock->getBlockId()) {
                $currentCmsBlock = [];
                $currentCmsBlock = $vendorcmsblock->getData();
                $shopurl = $this->cmsHelper->getVendorShopUrl();
                $currentCmsBlock['identifier'] = str_replace($shopurl, '', $vendorcmsblock->getIdentifier());
                $currentCmsBlock['status'] = $vendorcmsblock->getIsActive();
                $this->registry->register('current_cms_block', $currentCmsBlock);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(__('Edit Static Block'));
                return $resultPage;
            } else {
                $this->_redirect('*/*/');
            }
        } else {
            $this->_redirect('*/*/');
        }
    }
}
