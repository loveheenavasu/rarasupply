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

namespace Ced\Rewardsystem\Model\Source;

/**
 * Class Customer
 * @package Ced\Rewardsystem\Model\Source
 */
class Customer implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * Customer constructor.
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
    )
    {
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {

        $names = [];
        $customer = $this->customerCollectionFactory->create();
        foreach ($customer as $key => $val) {
            $names[] = array(
                'label' => $val->getName(),
                'value' => $val->getId()
            );
        }
        return $names;
    }
}
