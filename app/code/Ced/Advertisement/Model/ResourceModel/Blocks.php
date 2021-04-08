<?php

namespace Ced\Advertisement\Model\ResourceModel;

use Magento\Framework\Model\Resource\Db\AbstractDb;

class Blocks extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
     public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('ced_advertisement_block', 'id');
    }
}
