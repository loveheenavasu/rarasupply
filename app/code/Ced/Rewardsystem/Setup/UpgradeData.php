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

namespace Ced\Rewardsystem\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 * @package Ced\Rewardsystem\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if ($context->getVersion()
            && version_compare($context->getVersion(), '2.0.5') < 0
        ) {
            $update_sql = 'update ced_regisuserpoint t set t.updated_at = case
                        when t.is_register = 1 then t.creating_date
                        else (select q.updated_at from sales_shipment q where q.order_id = t.order_id limit 1)
                        end
                    where t.status = \'complete\' AND t.updated_at is null';
            $setup->run($update_sql);

            $update_sql = 'UPDATE `ced_regisuserpoint` SET `received_point`= `point` 
            WHERE `received_point` is null and `status` = \'complete\'';
            $setup->run($update_sql);
        }
        $setup->endSetup();
    }
}