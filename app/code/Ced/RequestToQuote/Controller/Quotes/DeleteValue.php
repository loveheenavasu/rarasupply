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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Ced\RequestToQuote\Model\RequestQuoteFactory;
use Magento\Customer\Model\Session;

class DeleteValue extends Action
{
    protected $session;

    protected $requestQuoteFactory;

    public function __construct(
        Context $context,
        Session $session,
        RequestQuoteFactory $requestQuoteFactory,
        array $data = []
    ) {
        $this->requestQuoteFactory = $requestQuoteFactory;
        $this->session = $session;
        parent::__construct ( $context, $data );
    }
    public function execute() {


        if (! $this->session->isLoggedIn ()) {
            $this->messageManager->addErrorMessage ( __ ( 'Please login first' ) );
            return $this->_redirect ( 'customer/account/login' );
        }
        if($id = $this->getRequest()->getParam('id')){
            $item = $this->requestQuoteFactory->create()->load($id);
            if ($item && $item->getId()) {
                $item->delete();
            }
        }
        $this->messageManager->addSuccessMessage (__('Item was deleted successfully'));
        return $this->_redirect('requesttoquote/cart/index');
    }
}