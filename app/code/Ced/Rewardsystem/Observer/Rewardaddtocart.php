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
namespace Ced\Rewardsystem\Observer;
 
use Magento\Framework\Event\ObserverInterface;
 
class Rewardaddtocart implements ObserverInterface
{
    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Registry $registry
        )
    {
        $this->_session = $session;
        $this->_coreRegistry = $registry;
    }
    /**
     * custom event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product    = $observer->getEvent()->getProduct();
        $pId        = $product->getId();
        $this->_session->setProductrewardid($pId);
        return ;
    }
}