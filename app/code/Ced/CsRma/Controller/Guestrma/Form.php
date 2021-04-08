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
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRma\Controller\Guestrma;

/**
 * Class Form
 * @package Ced\CsRma\Controller\Guestrma
 */
class Form extends \Magento\Framework\App\Action\Action
{
    /**
     * @param resultPageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Ced\CsRma\Helper\Guest
     */
    protected $guestHelper;

    /**
     * Form constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $session
     * @param \Ced\CsRma\Helper\Guest $guestHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session,
        \Ced\CsRma\Helper\Guest $guestHelper
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $session;
        $this->guestHelper = $guestHelper;
        parent::__construct($context);
    }

    /**
     * @param execute
     */
    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/');
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Returns Request'));
        $this->guestHelper->getBreadcrumbs($resultPage);
        return $resultPage;
    }
}



