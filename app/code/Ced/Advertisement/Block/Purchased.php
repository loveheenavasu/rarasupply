<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\Advertisement\Block;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Customer dashboard block
 *
 * @api
 * @since 100.0.2
 */
class Purchased extends \Magento\Framework\View\Element\Template
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $session,
        \Ced\Advertisement\Model\Purchased $purchased,
        array $data = []
    ) {        
        $this->_customerSession = $session;
        $this->_purchased = $purchased;        
        parent::__construct($context, $data);
    }

    public function getPurchasedColl(){        
        $customer_id = $this->_customerSession->getId();
        $coll = $this->_purchased->getCollection()
                                ->addFieldToFilter('customer_id',$customer_id);
        return $coll;
    }
}


