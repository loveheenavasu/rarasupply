<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     VendorsocialLogin
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

/**
 * VendorsocialLogin 	Google controller
 *
 * @category   	Ced
 * @package    	Ced_VendorsocialLogin
 * @author		CedCommerce Magento Core Team <connect@cedcommerce.com>
 */
namespace Ced\VendorsocialLogin\Controller;

use Magento\Framework\App\Action\NotFoundException;

abstract class ConnectResponse extends \Magento\Framework\App\Action\Action

{

    protected $_customerSessionFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory
    ){
        $this->_customerSessionFactory = $customerSessionFactory;
        parent::__construct($context);
    }


    protected function _sendResponse()
    {

        $resultRedirect = $this->resultRedirectFactory->create();
        $customerSession = $this->_customerSessionFactory->create();
        $url = $customerSession->getRefererUrl();
        $resultRedirect->setPath('customer/account/login');

        if(strpos( $url, 'csmarketplace'))
        {
            $resultRedirect->setPath('csmarketplace/account/login');
        }
        $customerSession->unsRefererUrl();
        return $resultRedirect;
    }
}