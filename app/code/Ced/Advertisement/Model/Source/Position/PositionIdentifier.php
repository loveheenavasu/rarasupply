<?php
 
namespace Ced\Advertisement\Model\Source\Position;
 
class PositionIdentifier extends \Magento\Eav\Model\Entity\Attribute\Source\Table
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
                                            ->getCollection();
        $options = [];
        foreach ($positions  as $key => $value) {
            $options[$value->getIdentifier()] = __($value->getPositionName()); 
        }     
        return $options;
    }

}
