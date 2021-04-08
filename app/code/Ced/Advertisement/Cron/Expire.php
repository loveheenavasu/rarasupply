<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Advertisement
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Advertisement\Cron;

class Expire {
 
    protected $_logger;

    protected $_objectManager;
 
    public function __construct(
    	\Psr\Log\LoggerInterface $logger,
    	\Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_logger = $logger;
        $this->_objectManager = $objectManager;
    }
 
    public function execute() {  
        try{  

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron_test12.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('EXPORT =====');	

        return '';
        
            $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $ced_advertisement_purchased_ads_table = $resource->getTableName('ced_advertisement_purchased_ads');

            /*Get all purchased plans which need to be expired*/
            $current_date = date("Y-m-d");
            $sql_ced_advertisement_purchased_ads_query = "SELECT `main_table`.* FROM `".$ced_advertisement_purchased_ads_table."` AS `main_table` WHERE (`status` = '1') AND (DATE(`created_at`) < (CURDATE() - INTERVAL `duration` DAY))";
            $connection->query($sql_ced_advertisement_purchased_ads_query);
            $data = $connection->fetchAll($sql_ced_advertisement_purchased_ads_query);
            $purchased_ids = [];
            $plan_ids = [];
            foreach ($data as $key => $value) {
                $purchased_ids[] = $value['id'];
                $plan_ids[] = $value['plan_id']; 
            }
            $ids = implode(',', $purchased_ids);
            $plan_ids = implode(',', $plan_ids);
            /*Update status as expired for expired items and increase qty in stock_item*/
            if(count($ids) && count($plan_ids)){            
                $sql_ced_advertisement_purchased_ads = "Update `".$ced_advertisement_purchased_ads_table ."` Set `status` = 0 where `id` IN (".$ids.")";
                $connection->query($sql_ced_advertisement_purchased_ads);

                $cataloginventory_stock_item_table = $resource->getTableName('cataloginventory_stock_item');
                $sql_cataloginventory_stock_item = "Update `".$cataloginventory_stock_item_table ."` AS `main_table` Set `qty` = (`qty` + 1) where `main_table`.`product_id` IN (".$plan_ids.")";
                $connection->query($sql_cataloginventory_stock_item);
            }
        }catch(\Exception $e){
            $this->_logger->critical($e->getMessage());
        }
    }
}