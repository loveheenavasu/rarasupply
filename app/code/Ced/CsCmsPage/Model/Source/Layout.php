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
  * @category  Ced
  * @package   Ced_CsCmsPage
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsCmsPage\Model\Source;

/**
 * @api
 * @since 100.0.2
 */
class Layout implements \Magento\Framework\Option\ArrayInterface
{
    public $_options = [];

    public function getAllOptions()
    {
        if(!$this->_options){
           $this->_options = [
                                ['value' => 'vendor-empty', 'label' => __('Vendor Empty Layout') ],
                                ['value' => 'vendorpanel', 'label' => __('Vendor Panel Layout') ],
                                ['value' => '1column', 'label' => __('1 column') ],
                                ['value' => '2columns-left', 'label' => __('2 columns with left bar') ],
                                ['value' => '2columns-right', 'label' => __('2 columns with right bar') ],
                                ['value' => '3columns', 'label' => __('3 columns') ],
                                ['value' => 'Empty', 'label' => __('Empty') ],
                            ];
        }
        return $this->_options;
    }
 
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    public function getOptionText($optionId){
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    
    public function getOptionArray(){
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }

    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

  
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        return $options;
    }
}
