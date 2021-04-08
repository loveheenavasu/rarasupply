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
 * @package     Ced_Rewardsystem
 * @author   	 CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Rewardsystem\Controller\Referral;

class Index extends \Magento\Framework\App\Action\Action {
	public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Customer\Model\Session $customerSession, array $data = []) {
		$this->resultPageFactory = $resultPageFactory;
		$this->_getSession = $customerSession;
		parent::__construct ( $context, $data );
	}
	public function execute() {
		if (! $this->_getSession->isLoggedIn ()) {
			$this->messageManager->addError ( __ ( 'Please login first' ) );
			$this->_redirect ( 'customer/account/login' );
			return;
		}
		$resultPage = $this->resultPageFactory->create ();
		return $resultPage;
	}
}