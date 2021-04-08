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

namespace Ced\Rewardsystem\Model;

use \Ced\Rewardsystem\Helper\Data;

/**
 * Class ReportDataprovider
 * @package Ced\Rewardsystem\Model
 */
class ReportDataprovider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * @var Data
     */
    protected $rewardsystem_helper;

    /**
     * ReportDataprovider constructor.
     * @param ResourceModel\Regisuserpoint\CollectionFactory $collectionFactory
     * @param Data $rewardsystem_helper
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        \Ced\Rewardsystem\Model\ResourceModel\Regisuserpoint\CollectionFactory $collectionFactory,
        Data $rewardsystem_helper,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $collectionFactory->create();
        $this->rewardsystem_helper = $rewardsystem_helper;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = $this->rewardsystem_helper->getCustomerWisePointSheet();
        $responseArray = [];
        $j = 0;

        foreach ($data as $customer_id => $point_details) {
            $responseArray[$j]['customer_id'] = $customer_id;
            $responseArray[$j]['point'] = !empty($point_details['points']) ? $point_details['points'] : 0;
            $responseArray[$j]['point_used'] = !empty($point_details['points_data']) ? array_sum(array_column($point_details['points_data'], 'point_used')) : 0;;
            $responseArray[$j]['earned_point'] = !empty($point_details['points_data']) ? array_sum(array_column($point_details['points_data'], 'point')) : 0;;
            $j++;
        }
        return [
            'totalRecords' => count($responseArray),
            'items' => $responseArray,
        ];
    }

}

