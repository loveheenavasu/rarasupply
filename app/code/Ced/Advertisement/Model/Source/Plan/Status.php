<?php
 
namespace Ced\Advertisement\Model\Source\Plan;
 
class Status extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * Retrieve Option values array
     *
     * @return array
     */
    public function toOptionArray($defaultValues = false)
    {
        $options = [];
        $options[1] = __('Enable');
        $options[2] = __('Disable');        
        return $options;
    }

}
