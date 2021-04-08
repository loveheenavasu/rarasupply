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
class Listi implements \Magento\Framework\Option\ArrayInterface{
	const PRODUCT_VIEW= 'view';
	const BOTH  = 'both';
	const CATEGORY_PAGE  = 'list';

    public function toOptionArray()
    {
        return array(
            self::BOTH   => __('List and View both'),
            self::CATEGORY_PAGE  => __('only List Page'),
            self::PRODUCT_VIEW   => __('only View Page'),
        );
    }
}