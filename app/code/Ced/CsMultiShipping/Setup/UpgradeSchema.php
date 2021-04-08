<?php


namespace Ced\CsMultiShipping\Setup;


use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->addChargeTransferToColumnInOrderTable($setup);
        }
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->modifyShippingMethodColumnType($setup);
        }

        $setup->endSetup();
    }

    private function modifyShippingMethodColumnType(SchemaSetupInterface $setup) {
        $connection = $setup->getConnection();
        //quote_address
        //quote_shipping_rate
        $column = 'shipping_method';
        foreach (['quote_address', 'sales_order'] as $table) {
            $tableName = $setup->getTable($table);
            if ($connection->isTableExists($tableName)) {
                if ($connection->tableColumnExists($tableName, $column)) {
                    $connection->modifyColumn(
                        $tableName,
                        $column,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => true
                        ]
                    );
                }
            }
        }

        $tableName = $setup->getTable('quote_shipping_rate');
        if ($connection->isTableExists($tableName)) {
            foreach (['code', 'method'] as $column) {
                if ($connection->tableColumnExists($tableName, $column)) {
                    $connection->modifyColumn(
                        $tableName,
                        $column,
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'nullable' => true,
                            'comment' => ucfirst($column)
                        ]
                    );
                }
            }
        }

    }

    private function addChargeTransferToColumnInOrderTable(SchemaSetupInterface $setup) {
        $connection = $setup->getConnection();

        $tableName = $setup->getTable('sales_order');
        if ($connection->isTableExists($tableName)) {
            $connection->addColumn(
                $tableName,
                'charge_transfer_to',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => true,
                    'comment' => 'Charge Transfer to - Admin/Vendor'
                ],
                null
            );
        }

    }
}
