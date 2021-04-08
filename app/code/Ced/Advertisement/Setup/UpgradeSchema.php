<?php

namespace Ced\Advertisement\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            $this->createBlockTable($setup);
            $this->createPlanOrderInfoTable($setup);
        }

        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $setup->getConnection()->addColumn($setup->getTable('sales_order'),
                'is_plan',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 2,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Is Plan'
                ]
            );
            $setup->getConnection()->addColumn($setup->getTable('sales_order_grid'),
                'is_plan',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 2,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Is Plan'
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.6', '<')) {
            $connection = $setup->getConnection();
            $connection->addColumn($setup->getTable('quote_item'),
                'block_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 12,
                    'nullable' => true,
                    'comment' => 'Block Id'
                ] 
            );     
        }
        $setup->endSetup();

    }  

    private function createBlockTable(SchemaSetupInterface $setup){
        $table = $setup->getConnection()->newTable($setup->getTable('ced_advertisement_block'))
                ->addColumn('id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Block Id'
                )->addColumn('title',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Block Title'
                )->addColumn('image',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Block Image'
                )->addColumn('status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    [],
                    'Status'
                )->addColumn('customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    [],
                    'Customer Id'
                )->addColumn('url',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Block Url'
                )->addIndex(
                    $setup->getIdxName('ced_advertisement_block', ['id']),
                    ['id']
                )->setComment('Block Table');
        $setup->getConnection()->createTable($table);
    }  

    private function createPlanOrderInfoTable(SchemaSetupInterface $setup){
        $table = $setup->getConnection()->newTable($setup->getTable('ced_advertisement_purchased_ads'))
                ->addColumn('id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Purchased Id'
                )->addColumn('order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    32,
                    [],
                    'Order Id'
                )->addColumn('customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    [],
                    'Customer Id'
                )->addColumn('status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    [],
                    'Status'
                )->addColumn('duration',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    [],
                    'Duration'
                )->addColumn('price',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false],
                    'Price'
                )->addColumn('plan_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    [],
                    'Purchased Plan Id'
                )->addColumn('block_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    [],
                    'Block Id'
                )->addColumn('plan_title',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Plan Title'
                )->addColumn('block_title',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Block Title'
                )->addColumn('block_image',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Block Image'
                )->addColumn('position_identifier',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Purchased plan position Identifier'
                )->addColumn('block_url',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [],
                    'Block Url'
                )->addColumn('created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At'
                )->addIndex(
                    $setup->getIdxName('ced_advertisement_purchased_ads', ['order_id']),
                    ['order_id']
                )->addIndex(
                    $setup->getIdxName('ced_advertisement_purchased_ads', ['customer_id']),
                    ['customer_id']
                )->addIndex(
                    $setup->getIdxName('ced_advertisement_purchased_ads', ['plan_id']),
                    ['plan_id']
                )->addIndex(
                    $setup->getIdxName('ced_advertisement_purchased_ads', ['block_id']),
                    ['block_id']
                )->setComment('Purchased Plans Table');
        $setup->getConnection()->createTable($table);
    }
}