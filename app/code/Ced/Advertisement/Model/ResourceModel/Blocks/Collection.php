<?php

namespace Ced\Advertisement\Model\ResourceModel\Blocks;

use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Ced\Advertisement\Model\Blocks','Ced\Advertisement\Model\ResourceModel\Blocks');
    }
}