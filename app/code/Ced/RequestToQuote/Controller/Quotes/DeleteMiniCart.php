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

namespace Ced\RequestToQuote\Controller\Quotes;

use Magento\Checkout\Model\Sidebar;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Checkout\Model\Cart as CustomerCart;

/**
 * Class DeleteMiniCart
 * @package Ced\RequestToQuote\Controller\Quotes
 */
class DeleteMiniCart extends \Magento\Checkout\Controller\Sidebar\RemoveItem
{
    /**
     * @var Sidebar
     */
    protected $sidebar;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * DeleteMiniCart constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @param Context $context
     * @param Sidebar $sidebar
     * @param LoggerInterface $logger
     * @param Data $jsonHelper
     * @param PageFactory $resultPageFactory
     * @param Validator $validator
     * @param CustomerCart $cart
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        Context $context,
        Sidebar $sidebar,
        LoggerInterface $logger,
        Data $jsonHelper,
        PageFactory $resultPageFactory,
        Validator $validator,
        CustomerCart $cart
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->sidebar = $sidebar;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->formKeyValidator = $validator;
        $this->cart = $cart;
        parent::__construct($context, $sidebar, $logger, $jsonHelper, $resultPageFactory);
    }

    /**
     * @return \Magento\Checkout\Controller\Sidebar\RemoveItem|\Magento\Framework\App\Response\Http|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try{
            $store = $this->storeManager->getStore();
        } catch (\Exception $e){
            $store = '';
        }
        $module_enable = $this->scopeConfig->getValue('requesttoquote_configuration/active/enable', ScopeInterface::SCOPE_STORE, $store->getId());
        $cartItemId = (int)$this->getRequest()->getParam('item_id');
        $quoteItem = $this->cart->getQuote()->getItemById($cartItemId);
        if((int)$module_enable && $quoteItem && $quoteItem->getItemId() && $quoteItem->getCedPoId()) {
            return $this->jsonResponse( __("You can not delete the quote item") );
        } else {
            if (!$this->getFormKeyValidator()->validate($this->getRequest())) {
                return $this->resultRedirectFactory->create()->setPath('*/cart/');
            }
            try {
                $this->sidebar->checkQuoteItem($cartItemId);
                $this->sidebar->removeQuoteItem($cartItemId);
                return $this->jsonResponse();
            } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                return $this->jsonResponse($exception->getMessage());
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
                return $this->jsonResponse($exception->getMessage());
            }
        }
    }

    /**
     * @return \Magento\Framework\Data\Form\FormKey\Validator
     * @deprecated 100.0.9
     */
    private function getFormKeyValidator()
    {
        return $this->formKeyValidator;
    }
}
