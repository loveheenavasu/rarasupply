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
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RequestToQuote\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_requestquote')
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Quote Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer Id'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Vendor Id'
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '1000',
            ['nullable' => false],
            'Customer Email'
        )->addColumn(
            'country',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '100',
            ['nullable' => false],
            'Country'
        )->addColumn(
            'state',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '60',
            ['nullable' => false],
            'State'
        )->addColumn(
            'city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '60',
            ['nullable' => false],
            'City'
        )->addColumn(
            'pincode',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Pincode'
        )->addColumn(
            'address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '100',
            ['nullable' => false],
            'Address'
        )->addColumn(
            'telephone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '20',
            ['nullable' => false],
            'Contact Info'
        )->addColumn(
            'quote_total_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Quoted Qty'
        )->addColumn(
            'quote_total_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['unsigned' => true, 'nullable' => false],
            'Quoted Price'
        )->addColumn(
            'quote_updated_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Quote Updated Qty'
        )->addColumn(
            'quote_updated_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['unsigned' => true, 'nullable' => false],
            'Quote Updated Price'
        )->addColumn(
            'shipping_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['unsigned' => true, 'nullable' => false],
            'Shipping Amount'
        )->addColumn(
            'shipment_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '50',
            ['nullable' => false],
            'Shipping Method'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            '0-Created and Pending,1-Processing,2-Approved,3-Cancelled,4-PO created,5-Partial Po, 6-Ordered,7-Complete'
        )->addColumn(
            'comments',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '1000',
            ['nullable' => false],
            'Comments'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'last_updated_by',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '1000',
            ['nullable' => false],
            'Last Updation done in quote value'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_requestquote_detail')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Quote Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Product Id'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Parent Id'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Vendor Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addColumn(
            'actual_unit_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['unsigned' => true, 'nullable' => false],
            'Original Unit Price'
        )->addColumn(
            'product_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Product Quantity'
        )->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['unsigned' => true, 'nullable' => false],
            'Original Quote Price'
        )->addColumn(
            'quote_updated_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Quote Updated Quantity'
        )->addColumn(
            'updated_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['unsigned' => true, 'nullable' => false],
            'Updated Amount'
        )->addColumn(
            'unit_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['unsigned' => true, 'nullable' => false],
            'Updated Unit Price'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Status 0-Unapproved 1- Approved 2- Partial Quantities 3- Cancelled'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'custom_option',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Custom Option'
        )->addColumn(
            'additional_data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Additional Data'
        )->addColumn(
            'remarks',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '1000',
            ['nullable' => false],
            'Remarks'
        )->addColumn(
            'last_updated_by',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '1000',
            ['nullable' => false],
            'Last Updation done in quote value'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_requestquote_message')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Quote Id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer Id'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '100',
            ['nullable' => false],
            'Vendor Id'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Message'
        )->addColumn(
            'sent_by',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Sent By'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_request_po_detail')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'po_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Po Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Order Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Quote Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Product Id'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Parent Id'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Vendor Id'
        )->addColumn(
            'product_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Product Qty'
        )->addColumn(
            'quoted_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Quoted Qty'
        )->addColumn(
            'quoted_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['unsigned' => true, 'nullable' => false ],
            'Quoted Price'
        )->addColumn(
            'po_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['unsigned' => true, 'nullable' => false ],
            'Po Price'
        )->addColumn(
            'remaining_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Remaining Qty'
        )->addColumn(
            'custom_option',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Custom Option'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            '0-Not Accepted,1-Accepted,1-Accepted and Ordered,2-Cancelled,3-Complete'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_request_po')
        )->addColumn(
            'po_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'po_increment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '100',
            ['nullable' => false],
            'Increment Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Order Id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Quote Id'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Vendor Id'
        )->addColumn(
            'quote_approved_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Quote Approved Qty'
        )->addColumn(
            'quote_approved_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['unsigned' => true, 'nullable' => false],
            'Quote Approved Amount'
        )->addColumn(
            'po_qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Po Qty'
        )->addColumn(
            'po_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['unsigned' => true, 'nullable' => false,  'default' => 0.00],
            'Po Amount'
        )->addColumn(
            'remaining_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['unsigned' => true, 'nullable' => false ],
            'Remaining Po Price'
        )->addColumn(
            'po_customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer Id'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            '0=Pending 1=Confirmed 2=Declined 3=Ordered'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
