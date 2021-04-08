<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Rewardsystem
 * @author     CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Rewardsystem\Model\Rule;

/**
 * Class Ruledataprovider
 * @package Ced\Rewardsystem\Model\Rule
 */
class Ruledataprovider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var \Ced\Rewardsystem\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $collection;

    /**
     * Ruledataprovider constructor.
     * @param \Ced\Rewardsystem\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        \Ced\Rewardsystem\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $collection = $this->collection->getData();
        return [
            'totalRecords' => $this->collection->getSize(),
            'items' => array_values($collection),
        ];
    }
}

