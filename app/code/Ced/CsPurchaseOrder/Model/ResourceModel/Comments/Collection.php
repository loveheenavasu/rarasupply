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
 * @package     Ced_CsPurchaseOrder
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsPurchaseOrder\Model\ResourceModel\Comments;

/**
 * Class Collection
 * @package Ced\CsPurchaseOrder\Model\ResourceModel\Comments
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    public function _construct()
    {
        $this->_init('Ced\CsPurchaseOrder\Model\Comments',
            'Ced\CsPurchaseOrder\Model\ResourceModel\Comments');
    }
}
