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
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Controller\Quotes;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Ced\RequestToQuote\Model\QuoteFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlFactory;


class View extends \Ced\CsMarketplace\Controller\Vendor
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
    		Context $context,
    		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
    		Session $customerSession,
    		UrlFactory $urlFactory,
    		\Magento\Framework\Registry $registry,
    		\Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
    		\Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
    		\Ced\CsMarketplace\Helper\Acl $aclHelper,
    		\Ced\CsMarketplace\Model\VendorFactory $vendor,
    		Registry $coreRegistry,
    		QuoteFactory $quoteFactory  
    ) {
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
        $this->_coreRegistry = $coreRegistry;
        $this->quoteFactory = $quoteFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $currentQuote = $this->quoteFactory->create()->load($id);
            if ($currentQuote && $currentQuote->getId()) {
                $this->_coreRegistry->register('current_quote', $currentQuote);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(__('Quote # %1', $currentQuote->getQuoteIncrementId()));
                return $resultPage;
            }
            $this->messageManager->addErrorMessage(__('This quote no longer exist.'));
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        }
        return $this->_redirect('rfq/quotes/index');
    }

}