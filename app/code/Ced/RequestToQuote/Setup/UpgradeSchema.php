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
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_requestquote'),
                'quote_increment_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, // Or any other type
                    'nullable' => true,
                    'comment' => 'Quote Increment Id',
                    'after' => 'quote_id'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_requestquote_detail'),
                'remaining_qty',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, // Or any other type
                    'nullable' => true,
                    'comment' => 'Quote remaining qty',
                    'after' => 'quote_updated_qty'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_requestquote'),
                'remaining_qty',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, // Or any other type
                    'nullable' => true,
                    'comment' => 'Quote remaining qty',
                    'after' => 'quote_updated_qty'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_requestquote'),
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Quote Store Id',
                    'after' => 'telephone'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {

            $setup->getConnection()->dropColumn($setup->getTable('ced_requestquote_detail'), 'additional_data');
            $setup->getConnection()->dropColumn($setup->getTable('ced_requestquote_detail'), 'remarks');

            $request_quote_table = $setup->getConnection()->newTable(
                $setup->getTable('request_quote')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['nullable' => true],
                ' Product Id'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [],
                'Customer Id'
            )->addColumn(
                'customer_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Customer Email'
            )->addColumn(
                'vendor_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [],
                'Customer Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                [],
                'Store Id'
            )->addColumn(
                'quote_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                [],
                'Quote Qty'
            )->addColumn(
                'quote_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,2',
                [],
                'Quote Price'
            )->addColumn(
                'product_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Product Type'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Product Name'
            )->addColumn(
                'sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Product Sku'
            )->addColumn(
                'custom_option',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'Custom Option'
            );
            $setup->getConnection()->createTable($request_quote_table);

            $setup->getConnection()->addColumn(
                $setup->getTable('ced_requestquote_detail'),
                'product_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'comment' => 'Product Type',
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_requestquote_detail'),
                'name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'comment' => 'Product Name',
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_requestquote_detail'),
                'sku',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'comment' => 'Product Sku',
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('ced_request_po_detail'),
                'product_type',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'comment' => 'Product Type',
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_request_po_detail'),
                'name',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'comment' => 'Product Name',
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('ced_request_po_detail'),
                'sku',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'comment' => 'Product Sku',
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $setup->getConnection()->dropColumn($setup->getTable('ced_requestquote'), 'comments');
            $setup->getConnection()->dropColumn($setup->getTable('ced_requestquote_detail'), 'actual_unit_price');
        }
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $setup->getConnection()->addColumn($setup->getTable('quote_item'),
                'ced_po_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Ced Po Id',
                ]
            );
        }
        $setup->endSetup();
    }
}