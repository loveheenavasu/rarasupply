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
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */ 
namespace Ced\CsRma\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
        /*add column in ced_csrma_chat */
        $tableChat = $installer->getTable('ced_csrma_chat');

        $installer->getConnection()->addColumn(
            $tableChat,
            'file',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Rma File'
            ]
        );

        /**
         * Create table 'ced_csrma_status'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csrma_status')
        )->addColumn(
            'status_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Password Id'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            [],
            'Status Name'
        )->addColumn(
            'sortOrder',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addColumn(
            'active',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Active'
        )->addColumn(
            'notification',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Notification'
        )->addColumn(
            'notify_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Notify Email'
        )->addColumn(
            'frontend_visible',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Frontend Visible'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '12,4',
            ['nullable' => false, 'default' => ''],
            'Vendor Id');

        $installer->getConnection()->createTable($table);

        /**
         * Add attribute_code in component attribute table
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('ced_csrma_request'),
                'is_transfered',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Is Transfered'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
        /*add column in ced_csrma_items */
            $tableChat = $installer->getTable('ced_csrma_items');

            $installer->getConnection()->addColumn(
                $tableChat,
                'sales_item_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Sales Item Id'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $tableReq = $installer->getTable('ced_csrma_request');

            $installer->getConnection()->addColumn(
                $tableReq,
                'reduce_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    'comment' => 'Reduce Amount'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableReq,
                'additional_refund',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    'comment' => 'Additional Refund Amount'
                ]
            );

            $installer->getConnection()->addColumn(
                $tableReq,
                'vendor_adjustment_amount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    'comment' => 'Vendor Adjustment Amount'
                ]
            );

            $table_chat = $installer->getTable('ced_csrma_items');

            $installer->getConnection()->addColumn(
                $table_chat,
                'row_total',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    'comment' => 'Row Total'
                ]
            );

        }
        $installer->endSetup();
    }
}

