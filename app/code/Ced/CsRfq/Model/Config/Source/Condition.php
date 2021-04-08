<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\CsRfq\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class Condition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'all', 'label' => __('For All Vendor')], ['value' => 'specific', 'label' => __('Let Vendor Choose Themselves')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['all' => __('For All Vendor'), 'specific'=> __('Let Vendor Choose Themselves')];
    }
}
