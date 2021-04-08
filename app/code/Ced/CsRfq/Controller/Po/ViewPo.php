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

namespace Ced\CsRfq\Controller\Po;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Ced\RequestToQuote\Model\PoFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlFactory;

/**
 * Class ViewPo
 * @package Ced\RequestToQuote\Controller\Adminhtml\Po
 */
class ViewPo extends \Ced\CsMarketplace\Controller\Vendor
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
     * @var PoFactory
     */
    protected $poFactory;

    /**
     * ViewPo constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param PoFactory $poFactory
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
            PoFactory $poFactory
    ) 	{
        	parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
        	$this->resultPageFactory = $resultPageFactory;
        	$this->_coreRegistry = $registry;
        	$this->poFactory = $poFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $currentPo = $this->poFactory->create()->load($id);
            if ($currentPo && $currentPo->getId()) {
                $this->_coreRegistry->register('current_po', $currentPo);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend('#'.$currentPo->getPoIncrementId());
                return $resultPage;
            } else {
                $this->messageManager->addErrorMessage(__('This Po no longer exist.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        }
        return $this->_redirect('rfq/po/index');
    }
    
}
    