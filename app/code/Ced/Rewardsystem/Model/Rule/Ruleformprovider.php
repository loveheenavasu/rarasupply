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
 * Class Ruleformprovider
 * @package Ced\Rewardsystem\Model\Rule
 */
class Ruleformprovider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry;

    /**
     * @var \Ced\Rewardsystem\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $collection;

    /**
     * @var \Ced\Rewardsystem\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * Ruleformprovider constructor.
     * @param \Ced\Rewardsystem\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
     * @param \Ced\Rewardsystem\Model\RuleFactory $ruleFactory
     * @param \Magento\Framework\Registry $registry
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        \Ced\Rewardsystem\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Ced\Rewardsystem\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Registry $registry,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $collectionFactory->create();
        $this->_coreRegistry = $registry;
        $this->ruleFactory = $ruleFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array|null
     */
    public function getData()
    {
        $arr = null;
        $id = $this->_coreRegistry->registry('rewardrule_form_data');
        if ($id) {
            $model = $this->ruleFactory->create()->load($id)->toArray();
            $arr = [$id => $model];
            return $arr;
        }
        return $arr;
    }

}