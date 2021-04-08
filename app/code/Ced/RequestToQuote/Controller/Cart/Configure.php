<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\RequestToQuote\Controller\Cart;

use Magento\Framework;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Configure
 * @package Ced\RequestToQuote\Controller\Cart
 */
class Configure extends \Magento\Checkout\Controller\Cart\Configure
{
    /**
     * @return Framework\Controller\Result\Redirect|Framework\View\Result\Page
     */
    public function execute()
    {   
        $id = (int)$this->getRequest()->getParam('id');
        $productId = (int)$this->getRequest()->getParam('product_id');
        $quoteItem = null;
        if ($id) {
            $quoteItem = $this->cart->getQuote()->getItemById($id);
        }

        try {
            if (!$quoteItem || $productId != $quoteItem->getProduct()->getId()) {
                $this->messageManager->addError(__("We can't find the quote item."));
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout/cart');
            }
            $module_enable = $this->_scopeConfig->getValue('requesttoquote_configuration/active/enable');
            if ((int)$module_enable && $quoteItem->getCedPoId() && $quoteItem->getProduct()->getId() == $productId) {
                $existPoId = $quoteItem->getCedPoId();
                $link = '<a href="'.$this->_url->getUrl('requesttoquote/customer/editpo', ['poId' => $existPoId]).'">'.__('Click Here').'</a>';
                $this->messageManager->addError(__('You can not update proposal item(s) from cart. '.$link.' to remove Proposal Item(s) from cart.'));
                return $this->_goBack();
            }
            return parent::execute();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot configure the product.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->_goBack();
        }
    }
}