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
class Blocks extends \Magento\Framework\View\Element\Template
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
        \Ced\Advertisement\Model\Blocks $blocks,
        array $data = []
    ) {
        $this->_customerSession = $session;
        $this->_blocks = $blocks;
        parent::__construct($context, $data);
    }

    public function getBlocksColl(){
        $customer_id = $this->getCustomerId();
        $coll = $this->_blocks->getCollection()->addFieldToFilter('customer_id',$customer_id);   
        return $coll;
    }

    public function getCustomerId(){
        return $this->_customerSession->getId();
    }

}
