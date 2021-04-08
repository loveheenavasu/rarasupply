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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'ced_csmarketplace_vendor_form_attribute'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_form_attribute')
        )->addColumn('id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn('attribute_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Id'
        )->addColumn('attribute_code',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Attribute Code'
        )->addColumn('is_visible',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Visible On Frontend'
        )->addColumn('sort_order',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Sort Order'
        )->addColumn('store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Store Id'
        )->setComment('Vendor Form Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ced_csmarketplace_vendor'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor')
        )->addColumn('entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn('entity_type_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Type ID'
        )->addColumn('attribute_set_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Set ID'
        )->addColumn('increment_id',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false, 'default' => ''],
            'Increment ID'
        )->addColumn('parent_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0'],
            'Parent ID'
        )->addColumn('created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn('updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addColumn('is_active',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '1'],
            'Is Active'
        )->addColumn('website_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Website ID'
        )->addIndex(
            $installer->getIdxName('ced_csmarketplace_vendor', ['parent_id']),
            ['parent_id']
        )->addIndex(
            $installer->getIdxName('ced_csmarketplace_vendor', ['website_id']),
            ['website_id']
        )->addForeignKey(
            $installer->getFkName('ced_csmarketplace_vendor', 'website_id', 'store_website', 'website_id'),
            'website_id',
            $installer->getTable('store_website'),
            'website_id',
            Table::ACTION_SET_NULL
        )->setComment('CsMarketplace Vendor');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ced_csmarketplace_vendor_datetime'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_datetime')
        )->addColumn('value_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Value Id'
        )->addColumn('attribute_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Attribute Id'
        )->addColumn('entity_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Entity Id'
        )->addColumn('value',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null],
            'Value'
        )->addIndex(
            $installer->getIdxName(
                'ced_csmarketplace_vendor_datetime',
                ['entity_id', 'attribute_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'attribute_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('ced_csmarketplace_vendor_datetime', ['attribute_id']),
            ['attribute_id']
        )->addIndex(
            $installer->getIdxName(
                'ced_csmarketplace_vendor_datetime',
                ['entity_id', 'attribute_id', 'value']
            ),
            ['entity_id', 'attribute_id', 'value']
        )->addForeignKey(
            $installer->getFkName(
                'ced_csmarketplace_vendor_datetime',
                'attribute_id',
                'eav_attribute',
                'attribute_id'
            ),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'ced_csmarketplace_vendor_datetime',
                'entity_id',
                'ced_csmarketplace_vendor',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('ced_csmarketplace_vendor'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment('CsMarketplace vendor Datetime');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ced_csmarketplace_vendor_decimal'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_decimal')
        )->addColumn(
            'value_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Value Id'
        )->addColumn(
            'attribute_id',
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
            'Attribute Id'
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'default' => '0'
            ],
            'Entity Id'
        )->addColumn(
            'value',
            Table::TYPE_DECIMAL,
            '12,4',
            [
                'nullable' => false,
                'default' => '0.0000'
            ],
            'Value'
        )->addIndex(
            $installer->getIdxName(
                'ced_csmarketplace_vendor_decimal',
                ['entity_id', 'attribute_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'attribute_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName(
                'ced_csmarketplace_vendor_decimal',
                ['attribute_id']
            ),
            ['attribute_id']
        )->addIndex(
            $installer->getIdxName(
                'ced_csmarketplace_vendor_decimal',
                ['entity_id', 'attribute_id', 'value']
            ),
            ['entity_id', 'attribute_id', 'value']
        )->addForeignKey(
            $installer->getFkName(
                'ced_csmarketplace_vendor_decimal',
                'attribute_id',
                'eav_attribute',
                'attribute_id'
            ),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'ced_csmarketplace_vendor_decimal',
                'entity_id',
                'ced_csmarketplace_vendor',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('ced_csmarketplace_vendor'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment('CsMarketplace vendor Decimal');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ced_csmarketplace_vendor_int'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_int')
        )->addColumn('value_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'identity' => true, 'primary' => true],
            'Value Id'
        )->addColumn('attribute_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'unsigned' => true, 'default' => '0'],
            'Attribute Id'
        )->addColumn('entity_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true, 'default' => '0'],
            'Entity Id'
        )->addColumn('value',
            Table::TYPE_INTEGER,
            null,
            ['default' => '0', 'nullable' => false],
            'Value'
        )->addIndex(
            $installer->getIdxName(
                'ced_csmarketplace_vendor_int',
                ['entity_id', 'attribute_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'attribute_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('ced_csmarketplace_vendor_int', ['attribute_id']),
            ['attribute_id']
        )->addIndex(
            $installer->getIdxName(
                'ced_csmarketplace_vendor_int',
                ['entity_id', 'attribute_id', 'value']
            ),
            ['entity_id', 'attribute_id', 'value']
        )->addForeignKey(
            $installer->getFkName(
                'ced_csmarketplace_vendor_int',
                'attribute_id',
                'eav_attribute',
                'attribute_id'
            ),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'ced_csmarketplace_vendor_int',
                'entity_id',
                'ced_csmarketplace_vendor',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('ced_csmarketplace_vendor'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment('CsMarketplace vendor Int');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ced_csmarketplace_vendor_text'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_text')
        )->addColumn('value_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'primary' => true, 'nullable' => false],
            'Value Id'
        )->addColumn('attribute_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => '0', 'nullable' => false],
            'Attribute Id'
        )->addColumn('entity_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => '0', 'nullable' => false],
            'Entity Id'
        )->addColumn(
            'value',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => false],
            'Value'
        )->addIndex(
            $installer->getIdxName(
                'ced_csmarketplace_vendor_text',
                ['entity_id', 'attribute_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'attribute_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('ced_csmarketplace_vendor_text', ['attribute_id']),
            ['attribute_id']
        )->addForeignKey(
            $installer->getFkName(
                'ced_csmarketplace_vendor_text',
                'attribute_id',
                'eav_attribute',
                'attribute_id'
            ),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'ced_csmarketplace_vendor_text',
                'entity_id',
                'ced_csmarketplace_vendor',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('ced_csmarketplace_vendor'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment('CsMarketplace vendor Text');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ced_csmarketplace_vendor_varchar'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_varchar')
        )->addColumn(
            'value_id',
            Table::TYPE_INTEGER,
            null,
            ['primary' => true,'identity' => true, 'nullable' => false],
            'Value Id'
        )->addColumn('attribute_id',
            Table::TYPE_SMALLINT,
            null,
            ['default' => '0', 'unsigned' => true, 'nullable' => false, ],
            'Attribute Id'
        )->addColumn('entity_id',
            Table::TYPE_INTEGER,
            null,
            ['default' => '0', 'unsigned' => true, 'nullable' => false,],
            'Entity Id'
        )->addColumn('value',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Value'
        )->addIndex(
            $installer->getIdxName(
                'ced_csmarketplace_vendor_varchar',
                ['entity_id', 'attribute_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id', 'attribute_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName('ced_csmarketplace_vendor_varchar', ['attribute_id']),
            ['attribute_id']
        )->addIndex(
            $installer->getIdxName(
                'ced_csmarketplace_vendor_varchar',
                ['entity_id', 'attribute_id', 'value']
            ),
            ['entity_id', 'attribute_id', 'value']
        )->addForeignKey(
            $installer->getFkName(
                'ced_csmarketplace_vendor_varchar',
                'attribute_id',
                'eav_attribute',
                'attribute_id'
            ),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'ced_csmarketplace_vendor_varchar',
                'entity_id',
                'ced_csmarketplace_vendor',
                'entity_id'
            ),
            'entity_id',
            $installer->getTable('ced_csmarketplace_vendor'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment('CsMarketplace vendor Varchar');
        $installer->getConnection()->createTable($table);

        /**
         *setup for Vendor Payment
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_payments')
        )->addColumn('entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn('vendor_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => 0],
            'Vendor Id'
        )->addColumn('transaction_id',
            Table::TYPE_TEXT,
            '200',
            ['nullable' => true, 'default' => null],
            'Transaction Id'
        )->addColumn('amount_desc',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Amount Description'
        )->addColumn('amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => null],
            'Amount'
        )->addColumn('base_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Base Amount'
        )->addColumn('currency',
            Table::TYPE_TEXT,
            '10',
            ['nullable' => false],
            'Currency'
        )->addColumn('fee',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => 0],
            'Fee'
        )->addColumn('base_fee',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Base Fee'
        )->addColumn('net_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => 0],
            'Net Amount'
        )->addColumn('base_net_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Base Net Amount'
        )->addColumn('balance',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Balance'
        )->addColumn('base_balance',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Base Balance'
        )->addColumn('tax',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => 0],
            'Tax'
        )->addColumn('base_tax',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Base Tax'
        )->addColumn('notes',
            Table::TYPE_TEXT,
            '500',
            ['nullable' => true, 'default' => null],
            'Notes'
        )->addColumn('transaction_type',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Transaction Type'
        )->addColumn('payment_method',
            Table::TYPE_TEXT,
            '300',
            ['nullable' => true, 'default' => null],
            'Payment Method'
        )->addColumn('payment_code',
            Table::TYPE_TEXT,
            '200',
            ['nullable' => true, 'default' => null],
            'Payment Code'
        )->addColumn('payment_detail',
            Table::TYPE_TEXT,
            '500',
            ['nullable' => false],
            'Payment Detail'
        )->addColumn('status',
            Table::TYPE_TEXT,
            '10',
            ['nullable' => true, 'default' => null],
            'Status'
        )->addColumn('payment_date',
            Table::TYPE_DATETIME,
            null,
            [],
            'Payment Date'
        )->addColumn('created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Created At'
        )->addColumn('base_currency',
            Table::TYPE_TEXT,
            '10',
            ['nullable' => true, 'default' => null],
            'Base Currency'
        )->addColumn('base_to_global_rate',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => null],
            'Base To Global Rate'
        )->setComment('CsMarketplace Vendor Payment Table');
        $installer->getConnection()->createTable($table);

        /**
         *  setup for Vendor Settings
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_settings')
        )->addColumn('setting_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Setting Id'
        )->addColumn('vendor_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Vendor Id'
        )->addColumn('group',
            Table::TYPE_TEXT,
            '32',
            ['nullable' => false],
            'Group'
        )->addColumn('key',
            Table::TYPE_TEXT,
            '64',
            ['nullable' => false],
            'Key'
        )->addColumn('value',
            Table::TYPE_TEXT,
            '500',
            ['nullable' => false],
            'value'
        )->addColumn('serialized',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'serialized'
        )->setComment('CsMarketplace Vendor Settings');
        $installer->getConnection()->createTable($table);

        /**
         *  Vendor Order Setup
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_sales_order')
        )->addColumn('id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn('vendor_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Vendor Id'
        )->addColumn('order_id',
            Table::TYPE_TEXT,
            30,
            ['nullable' => false],
            'Order Id'
        )->addColumn('currency',
            Table::TYPE_TEXT,
            '10',
            ['nullable' => false],
            'Currency'
        )->addColumn('base_order_total',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Base Order Total'
        )->addColumn('order_total',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => null],
            'Order Total'
        )->addColumn('shop_commission_type_id',
            Table::TYPE_TEXT,
            '500',
            ['nullable' => false],
            'Shop Commission Type'
        )->addColumn('shop_commission_rate',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'shop_commission_rate'
        )->addColumn('shop_commission_base_fee',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Shop Commission Base Fee'
        )->addColumn('shop_commission_fee',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Shop Commission Fee'
        )->addColumn('product_qty',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Product Qty'
        )->addColumn('order_payment_state',
            Table::TYPE_TEXT,
            '500',
            ['nullable' => false],
            'Order Payment State'
        )->addColumn('payment_state',
            Table::TYPE_TEXT,
            '11',
            ['nullable' => false],
            'Payment State'
        )->addColumn('billing_country_code',
            Table::TYPE_TEXT,
            '100',
            ['nullable' => false],
            'Billing Country Code'
        )->addColumn('shipping_country_code',
            Table::TYPE_TEXT,
            '100',
            ['nullable' => false],
            'Shipping Country Code'
        )->addColumn('base_currency',
            Table::TYPE_TEXT,
            '10',
            ['nullable' => true, 'default' => null],
            'Base Currency'
        )->addColumn('base_to_global_rate',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => null],
            'Base To Global Rate'
        )->addColumn('items_commission',
            Table::TYPE_TEXT,
            '500',
            ['unsigned' => true, 'nullable' => true, 'default' => null],
            'Items Commission'
        )->addColumn('shipping_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => 0],
            'Shipping Amount'
        )->addColumn('base_shipping_amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => 0],
            'Base Shipping Amount'
        )->addColumn('shipping_paid',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => 0],
            'Shipping Paid'
        )->addColumn('shipping_refunded',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => 0],
            'Shipping Refunded'
        )->addColumn('method',
            Table::TYPE_TEXT,
            '255',
            ['nullable' => false, 'default' => null],
            'Shipping Method'
        )->addColumn('method_title',
            Table::TYPE_TEXT,
            '300',
            ['nullable' => true, 'default' => null],
            'Shipping Method Title'
        )->addColumn('carrier',
            Table::TYPE_TEXT,
            '255',
            ['nullable' => true, 'default' => null],
            'Shipping Carrier'
        )->addColumn('carrier_title',
            Table::TYPE_TEXT,
            '255',
            ['nullable' => true, 'default' => null],
            'Shipping Carier Title'
        )->addColumn('code',
            Table::TYPE_TEXT,
            '255',
            ['nullable' => true, 'default' => null],
            'Shipping Code'
        )->addColumn('shipping_description',
            Table::TYPE_TEXT,
            '255',
            ['nullable' => true, 'default' => null],
            'Shipping Description'
        )->addColumn('vorders_mode',
            Table::TYPE_TEXT,
            '255',
            ['nullable' => true, 'default' => null],
            'Vorder Mode'
        )->addIndex(
            $installer->getIdxName('ced_csmarketplace_vendor_sales_order',
                ['vendor_id', 'order_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['vendor_id', 'order_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment('CsMarketplace Vendor Order');
        $installer->getConnection()->createTable($table);

        /**
         *  Vendor Shop Setup
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_shop')
        )->addColumn('id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn('vendor_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Vendor Id'
        )->addColumn('shop_disable',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Shop Disable'
        )->setComment('CsMarketplace Vendor Shop');
        $installer->getConnection()->createTable($table);

        /**
         *  Add vendor_id column into core table sales_order_item
         */
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_item'),
            'vendor_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Vendor Id'
        );

        /**
         *  Vendor Products Table Setup
         */

        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_products')
        )->addColumn('id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn('vendor_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Vendor Id'
        )->addColumn('product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Product Id'
        )->addColumn('type',
            Table::TYPE_TEXT,
            '255',
            ['nullable' => true, 'default' => null],
            'Type'
        )->addColumn('price',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => 0],
            'Price'
        )->addColumn('special_price',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true, 'default' => 0],
            'Special Price'
        )->addColumn('name',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Name'
        )->addColumn('description',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Description'
        )->addColumn('short_description',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Short Description'
        )->addColumn('sku',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'SKU'
        )->addColumn('weight',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false],
            'Weight'
        )->addColumn('check_status',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => 0],
            'Check Status'
        )->addColumn('qty',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => 0],
            'Quantity'
        )->addColumn('is_in_stock',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => 0],
            'Quantity'
        )->addColumn('website_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => 0],
            'Website ID'
        )->addColumn('is_multiseller',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => 0],
            'Is MultiSeller Product'
        )->addColumn('parent_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => 0],
            'Parent Id'
        )->setComment('CsMarketplace Vendor Product');
        $installer->getConnection()->createTable($table);

        /**
         * Vendor Product Status Setup
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmarketplace_vendor_products_status')
        )->addColumn('id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn('vendor_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Vendor Id'
        )->addColumn('product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Product Id'
        )->addColumn('store_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addColumn('status',
            Table::TYPE_TEXT,
            '64',
            ['nullable' => true, 'default' => null],
            'Status'
        )->addIndex(
            $installer->getIdxName(
                'ced_csmarketplace_vendor_products_status',
                ['vendor_id', 'product_id', 'store_id', 'status'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['vendor_id', 'product_id', 'store_id', 'status'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment('CsMarketplace Vendor Product Status');
        $installer->getConnection()->createTable($table);
        $installer->endSetup();

    }
}
