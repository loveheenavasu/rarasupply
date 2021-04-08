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

/**
 * Class ReportDataprovider
 * @package Ced\Rewardsystem\Model
 */
class ReportDataprovider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var RegisuserpointFactory
     */
    protected $regisuserpointFactory;

    /**
     * ReportDataprovider constructor.
     * @param RegisuserpointFactory $regisuserpointFactory
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        \Ced\Rewardsystem\Model\RegisuserpointFactory $regisuserpointFactory,
        \Ced\Rewardsystem\Model\ResourceModel\Regisuserpoint\CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    )
    {
        $this->regisuserpointFactory = $regisuserpointFactory;
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $collection = $this->regisuserpointFactory->create()->getCollection()->getData();

        $data = [];
        $responseArray = [];
        foreach ($collection as $key => $value) {
            $data[$value['customer_id']][] = $value;
        }
        $j = 0;

        foreach ($data as $k => $v) {
            $points = 0;
            $points_used = 0;
            if ($k == 0) {
                continue;
            }

            for ($i = 0; $i < count($v); $i++) {
                if ($v[$i]['status'] == "complete") {
                    $points = $points + $v[$i]['point'];
                }
                $points_used = $points_used + $v[$i]['point_used'];
            }
            if ($points == 0 && $points_used == 0) {
                continue;
            }
            $responseArray[$j]['customer_id'] = $k;
            $responseArray[$j]['point'] = $points;
            $responseArray[$j]['point_used'] = $points_used;
            $j++;
        }

        return [
            'totalRecords' => count($responseArray),
            'items' => $responseArray,
        ];
    }
}

