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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package Ced\Rewardsystem\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $config;

    /**
     * UpgradeSchema constructor.
     * @param \Magento\Config\Model\ResourceModel\Config $config
     */
    public function __construct(\Magento\Config\Model\ResourceModel\Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('ced_regisuserpoint');
        if ($installer->getConnection()->isTableExists($tableName) == true) {
            $connection = $setup->getConnection();
            $connection
                ->addColumn(
                    $tableName,
                    'parent_customer',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    array('nullable' => false),
                    'parent_customer'
                );
            $connection->addColumn(
                $tableName,
                'refer_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                array('nullable' => false),
                'refer_code'
            );
            $connection->addColumn(
                $tableName,
                'is_register',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                array('nullable' => false),
                'is_register'
            );
            $connection->addColumn(
                $tableName,
                'is_birthday',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                array('nullable' => false),
                'is_birthday'
            );
            $connection->addColumn(
                $tableName,
                'follow_on_insta',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                array('nullable' => false),
                'follow_on_insta'
            );
        }


        $configModel = $this->config;
        $configModel->saveConfig('customer/address/dob_show', 'opt', 'default', 0);

        if (version_compare($context->getVersion(), '2.0.4') < 0) {

            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'rewardsystem_base_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '10,4',
                    'nullable' => false,
                    'comment' => 'Reward Base Amount'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'rewardsystem_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '10,4',
                    'nullable' => false,
                    'comment' => 'Reward Discount'
                ]
            );

        }

        if (version_compare($context->getVersion(), '2.0.5') < 0) {
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_regisuserpoint'),
                'updated_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'nullable' => true,
                    'comment' => 'The point approval/cancellation date'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('ced_regisuserpoint'),
                'item_details',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 225,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'The product wise point details'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('ced_regisuserpoint'),
                'received_point',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'nullable' => true,
                    'comment' => 'The point received after successful approval'
                ]
            );

        }

        $installer->endSetup();
    }
}
