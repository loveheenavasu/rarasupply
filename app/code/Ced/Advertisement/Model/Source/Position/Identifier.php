<?php
 
namespace Ced\Advertisement\Model\Source\Position;
 
class Identifier extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * Retrieve Option values array
     *
     * @return array
     */
    public function toOptionArray($defaultValues = false)
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $positions  = $this->_objectManager->create('Ced\Advertisement\Model\Positions')
                                            ->getCollection()
                                            ->addFieldToFilter('position_status',1);
        $options = [];
        foreach ($positions  as $key => $value) {
            $options[$value->getIdentifier()] = __($value->getPositionName()); 
        }     
        return $options;
    }

}
