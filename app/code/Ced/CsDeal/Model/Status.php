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
 * @package     Ced_CsDeal
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Model;
class Status implements \Magento\Framework\Option\ArrayInterface{
	const STATUS_ENABLED	= 'enabled';
    const STATUS_DISABLED	= 'disabled';
    const STATUS_EXPIRED	= 'expired';
	
 public function toOptionArray()
    {
       return array(
            self::STATUS_ENABLED    => __('Enabled'),
            self::STATUS_DISABLED   => __('Disabled'),
            self::STATUS_EXPIRED   => __('Expired')
        );
    }
}