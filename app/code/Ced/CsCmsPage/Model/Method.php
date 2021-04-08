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
  * @package     Ced_CsCmsPage
  * @author   CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsCmsPage\Model;

class Method implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 7, 'label' => __('please Select Layout')],
            ['value' => 'Vendor Empty Layout', 'label' => __('Vendor Empty Layout')],
            ['value' => 'Vendor Panel Layout', 'label' => __('Vendor Panel Layout')],
            ['value' => '1 column', 'label' => __('1 column')],
            ['value' =>'1 columns left bar', 'label' => __('1 columns left bar')],
            ['value' => '1 columns right bar', 'label' => __('1 columns right bar')],
            ['value' => '3 columns', 'label' => __('3 columns')],
            ['value' => 'Empty', 'label' => __('Empty')]
        ];
    }
}
