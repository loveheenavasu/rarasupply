<?php

namespace Ced\Advertisement\Model\Source;

class Position extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
	/**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
      $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      
      $position_collection = $this->_objectManager->create('Ced\Advertisement\Model\Positions')
                                                  ->getCollection();                                            
      
      $options[] = ['label'=>'Please select Position to show Ad','value'=>''];
      foreach ($position_collection as $key => $value) {
        $options[] = ['label' =>__($value->getPositionName()), 'value' => $value->getIdentifier()];
      }
      return $options;
    }
 
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

     /**
     * Get Address Type
     *
     * @param string $optionId
     * @return null|string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }

   
    /**
     * Get Address Type array for option element
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Get Address Type labels array with empty value
     *
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }
}