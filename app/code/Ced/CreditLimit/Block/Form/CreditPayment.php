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
  * @category  Ced
  * @package   Ced_CreditLimit
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      https://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CreditLimit\Block\Form;
/**
 * Class CreditPayment
 * @package Ced\CreditLimit\Block\Form
 */
class CreditPayment extends \Magento\Payment\Block\Form
{
    /**
     * @var string
     */
    protected $_template = 'Ced_CreditLimit::form/creditlimit.phtml';

   public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
             \Magento\Customer\Model\Session $session,
    		\Magento\Framework\Pricing\Helper\Data $pricingHelper,
    		\Ced\CreditLimit\Helper\Data $credithelper,
    		array $data = []
    	)
    {
    	$this->pricingHelper = $pricingHelper;
        $this->customerSession = $session;
        $this->helper = $credithelper;
        parent::__construct($context,$data);
    }

    /**
     * @return mixed
     */
    public function getRemainingLimit()
    {
        $creditCollection = $this->helper->getCustomerCreditLimit($this->customerSession->getCustomerId());
        $formattedPrice = $this->pricingHelper->currency($creditCollection->getRemainingAmount(), true, false);
        return $amount['amount'] = $formattedPrice;
    }
}
