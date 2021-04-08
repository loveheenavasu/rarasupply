<?php

namespace Ced\CsMarketplace\Model\ResourceModel;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class TransactionCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'ced_csmarketplace_vendor_payments',
        $resourceModel = 'Ced\CsMarketplace\Model\ResourceModel\Vpayment',
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttribute
    ) {
        $this->_eavAttribute = $eavAttribute;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $vendorAttributeId = $this->_eavAttribute->create()->getIdByCode('csmarketplace_vendor', 'public_name');

        $this->getSelect()->joinLeft($this->getTable('ced_csmarketplace_vendor_varchar'), 'main_table.vendor_id='.$this->getTable('ced_csmarketplace_vendor_varchar').'.entity_id AND '.$this->getTable('ced_csmarketplace_vendor_varchar').'.attribute_id='.$vendorAttributeId, ['vendor_name' => 'value']);;


        $this->addFilterToMap('vendor_name', $this->getTable('ced_csmarketplace_vendor_varchar').'.value');

        return $this;
    }

}
