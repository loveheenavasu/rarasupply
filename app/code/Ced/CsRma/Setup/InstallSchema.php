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
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRma\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Ced\CsRma\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * InstallSchema constructor.
     * @param \Magento\Config\Model\ResourceModel\Config $config
     */
    public function __construct(\Magento\Config\Model\ResourceModel\Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'ced_csrma_request'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csrma_request')
        )->addColumn(
            'rma_request_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rma Request Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Store Id'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Website Id'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addColumn(
            'rma_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            [],
            'Rma Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            [],
            'Order Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => null],
            'Customer Id'
        )->addColumn(
            'customer_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Name'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Email'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            [],
            'Status'
        )->addColumn(
            'approval_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Approval Code'
        )->addColumn(
            'external_link',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'External Link'
        )->addColumn(
            'package_condition',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Package Condition'
        )->addColumn(
            'reason',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Reason'
        )->addColumn(
            'resolution_requested',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Resolution Requested'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '12,4',
            ['nullable' => false, 'default' => ''],
            'Vendor Id'
        )
            ->addColumn(
                'print_lable',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Print Lable'
            )->addIndex(
                $installer->getIdxName(
                    'ced_csrma_request',
                    ['external_link'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['external_link'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment(
                'Cs Rma Request'
            );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ced_csrma_chat'
         */

        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csrma_chat')
        )->addColumn(
            'rma_chat_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rma Chat Id'
        )->addColumn(
            'rma_request_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Rma Request Id'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'chat_flow',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Chat Flow'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '12,4',
            ['nullable' => false, 'default' => ''],
            'Vendor Id'
        )
            ->addColumn(
                'chat',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Chat'
            )->addColumn(
                'seen',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Seen'
            )->addIndex(
                $installer->getIdxName('ced_csrma_chat', ['rma_request_id']),
                ['rma_request_id']
            )->addForeignKey(
                $installer->getFkName('ced_csrma_chat', 'rma_request_id', 'ced_csrma_request', 'rma_request_id'),
                'rma_request_id',
                $installer->getTable('ced_csrma_request'),
                'rma_request_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Cs RMA Chat'
            );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ced_csrma_notification'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csrma_notification')
        )->addColumn(
            'rma_notification_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rma Notification Id'
        )->addColumn(
            'rma_request_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Rma Request Id'
        )->addColumn(
            'notification_message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Notification Message'
        )
            ->addColumn(
                'vendor_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '12,4',
                ['nullable' => false, 'default' => ''],
                'Vendor Id'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'owner',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false,
                    'default' => 0],
                'Owner of Notification'
            )->addIndex(
                $installer->getIdxName('ced_csrma_notification', ['rma_request_id']),
                ['rma_request_id']
            )->addForeignKey(
                $installer->getFkName('ced_csrma_notification', 'rma_request_id', 'ced_csrma_request', 'rma_request_id'),
                'rma_request_id',
                $installer->getTable('ced_csrma_request'),
                'rma_request_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Cs RMA Notification'
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ced_csrma_items'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csrma_items')
        )->addColumn(
            'rma_items_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rma Item Id'
        )->addColumn(
            'rma_request_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Rma Request Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Product Id'
        )->addColumn(
            'sku',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            ['nullable' => false],
            'SKU'
        )->addColumn(
            'item_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Item Name'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '12,4',
            ['nullable' => false, 'default' => ''],
            'Vendor Id'
        )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                NULL,
                [],
                'Price'
            )->addColumn(
                'rma_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Rma Qty'
            )->addColumn(
                'qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Qty'
            )->addIndex(
                $installer->getIdxName('ced_csrma_items', ['rma_request_id']),
                ['rma_request_id']
            )->addForeignKey(
                $installer->getFkName('ced_csrma_items', 'rma_request_id', 'ced_csrma_request', 'rma_request_id'),
                'rma_request_id',
                $installer->getTable('ced_csrma_request'),
                'rma_request_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Cs RMA Item'
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ced_csrma_shipping'
         */

        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csrma_shipping')
        )->addColumn(
            'rma_shippping_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rma Shippping Id'
        )->addColumn(
            'rma_request_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Rma Request Id'
        )->addColumn(
            'customer_firstname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Customer FirstName'
        )->addColumn(
            'customer_lastname',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Customer LastName'
        )->addColumn(
            'customer_middlename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Customer MiddleName'
        )->addColumn(
            'address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Address'
        )->addColumn(
            'dest_country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false, 'default' => 0],
            'Address'
        )->addColumn(
            'dest_region',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            ['nullable' => false, 'default' => 0],
            'Destination Region'
        )->addColumn(
            'dest_zip',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false, 'default' => 0],
            'Destination Zip'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '12,4',
            ['nullable' => false, 'default' => ''],
            'Vendor Id'
        )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addIndex(
                $installer->getIdxName('ced_csrma_shipping', ['rma_request_id']),
                ['rma_request_id']
            )->addForeignKey(
                $installer->getFkName('ced_csrma_shipping', 'rma_request_id', 'ced_csrma_request', 'rma_request_id'),
                'rma_request_id',
                $installer->getTable('ced_csrma_request'),
                'rma_request_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Cs RMA Shipping'
            );

        $installer->getConnection()->createTable($table);

        /*create ced_csrma_status*/
        $installer->endSetup();

        /**
         * save serialize config data
         */
        $reasons = 'a:4:{s:18:"_1513924355266_266";a:1:{s:7:"reasons";s:15:"Defcetive Piece";}s:18:"_1513924371728_728";a:1:{s:7:"reasons";s:14:"Seal is Broken";}s:18:"_1513924383184_184";a:1:{s:7:"reasons";s:18:"Product is damaged";}s:18:"_1513924391518_518";a:1:{s:7:"reasons";s:29:"The Product is of Low Quality";}}';

        $resolution = 'a:3:{s:18:"_1469452570562_562";a:1:{s:10:"resolution";s:7:"Replace";}s:18:"_1469452573746_746";a:1:{s:10:"resolution";s:6:"Repair";}s:18:"_1469452581114_114";a:1:{s:10:"resolution";s:6:"Refund";}}';

        $conditions = 'a:3:{s:18:"_1469452593594_594";a:1:{s:10:"conditions";s:4:"Open";}s:18:"_1469452604233_233";a:1:{s:10:"conditions";s:6:"Closed";}s:18:"_1469452611826_826";a:1:{s:10:"conditions";s:7:"Damaged";}}';

        $configModel = $this->config;
        $configModel->saveConfig('ced_csmarketplace/rma_general_group/reasons', $reasons, 'default', 0);
        $configModel->saveConfig('ced_csmarketplace/rma_general_group/resolution', $resolution, 'default', 0);
        $configModel->saveConfig('ced_csmarketplace/rma_general_group/conditions', $conditions, 'default', 0);

    }
}
