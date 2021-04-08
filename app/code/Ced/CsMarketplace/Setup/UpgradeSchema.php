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

use Ced\CsMarketplace\Model\ResourceModel\Vproducts\CollectionFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package Ced\CsMarketplace\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * UpgradeSchema constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('ced_csmarketplace_vendor_form_attribute');
        $tableName_vendor = $installer->getTable('ced_csmarketplace_vendor');
        // phpcs:disable Magento2.Files.LineLength.MaxExceeded
        if (version_compare($context->getVersion(), '0.0.9', '<')) {
            $connection = $setup->getConnection();
            if ($installer->getConnection()->isTableExists($tableName) == true) {
                $connection->addColumn(
                    $tableName,
                    'use_in_registration',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'Use in Registration Form'
                    ],
                    0
                );

                $connection->addColumn(
                    $tableName,
                    'position_in_registration',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'Position in Registration Form'
                    ],
                    0
                );

                $connection->addColumn(
                    $tableName,
                    'use_in_left_profile',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'Use in Left Profile'
                    ],
                    0
                );

                $connection->addColumn(
                    $tableName,
                    'position_in_left_profile',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'unsigned' => true,
                        'nullable' => false,
                        'default' => '0',
                        'length'  => 50,
                        'comment' => 'Position in Left Profile'
                    ],
                    0
                );

                $connection->addColumn(
                    $tableName,
                    'fontawesome_class_for_left_profile',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => false,
                        'length' => 200,
                        'comment' => 'Fontawesome class for Left Profile'
                    ],
                    null
                );
            }

            if ($installer->getConnection()->isTableExists($tableName_vendor) == true) {
                $connection->addColumn(
                    $tableName_vendor,
                    'address',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => false,
                        'comment' => 'Address'
                    ],
                    null
                );

                $connection->addColumn(
                    $tableName_vendor,
                    'city',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => false,
                        'comment' => 'City'
                    ],
                    null
                );

                $connection->addColumn(
                    $tableName_vendor,
                    'zip_code',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => false,
                        'comment' => 'Zip/Postal Code'
                    ],
                    null
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $tableName = $installer->getTable('ced_csmarketplace_vendor_products');

            if ($installer->getConnection()->isTableExists($tableName) == true) {
                $this->collectionFactory->create()
                    ->addFieldToFilter('check_status', 3)
                    ->walk('delete');
                $connection = $setup->getConnection();
                $connection->rawQuery("ALTER TABLE `" . $tableName . "` ADD UNIQUE(`product_id`)");
            }
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $tableName = $installer->getTable('ced_csmarketplace_vendor_products');

            if ($installer->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();

                $connection->rawQuery(
                    "ALTER TABLE `" . $tableName .
                    "`  ADD CONSTRAINT `ced_csmarketplace_vendor_product_vendor_id_to_fk`".
                    " FOREIGN KEY (`vendor_id` ) REFERENCES `" .
                    $installer->getTable('ced_csmarketplace_vendor') .
                    "` (`entity_id` ) ON DELETE CASCADE"
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $table = $installer->getTable('ced_csmarketplace_vendor_sales_order');

            if ($installer->getConnection()->isTableExists($table) == true) {
                $columns = [
                    'billing_name' => [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => false,
                        'comment' => 'Billing Name'
                    ],
                    'created_at' => [
                        'type' => Table::TYPE_TIMESTAMP,
                        'nullable' => false,
                        'default' => Table::TIMESTAMP_INIT,
                        'comment' => 'Created At'
                    ]
                ];
                $connection = $installer->getConnection();
                foreach ($columns as $name => $value) {
                    $connection->addColumn($table, $name, $value);
                }
            }
        }

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $vendorProduct = $installer->getTable('ced_csmarketplace_vendor_products');
            $connection = $setup->getConnection();

            if ($installer->getConnection()->isTableExists($vendorProduct) == true) {
                $connection->rawQuery("ALTER TABLE `" . $vendorProduct .
                    "`  ADD CONSTRAINT `ced_csmarketplace_vendor_products_product_id_to_fk`".
                    " FOREIGN KEY (`product_id` ) REFERENCES `" .
                    $installer->getTable('catalog_product_entity') .
                    "` (`entity_id` ) ON DELETE CASCADE");
            }

            $vendorProductStatus = $installer->getTable('ced_csmarketplace_vendor_products_status');

            if ($installer->getConnection()->isTableExists($vendorProductStatus) == true) {
                $connection->rawQuery("ALTER TABLE `" . $vendorProductStatus .
                    "`  ADD CONSTRAINT `ced_csmarketplace_vendor_products_status_product_id_to_fk`".
                    " FOREIGN KEY (`product_id` ) REFERENCES `" .
                    $installer->getTable('catalog_product_entity') .
                    "` (`entity_id` ) ON DELETE CASCADE");
            }

            /**
             * Create table 'ced_csmarketplace_notification'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('ced_csmarketplace_notification'))
                ->addColumn('id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn('action',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'action'
                )->addColumn('title',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'title'
                )->addColumn('itag',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'itag'
                )->addColumn('vendor_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'vendor_id'
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
                )->addColumn('reference_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'reference_id'
                )->addColumn('status',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'default' => '0'],
                    'status'
                )->addIndex(
                    $installer->getIdxName(
                        'ced_csmarketplace_notifications_vendor_id',
                        ['vendor_id']
                    ),
                    ['id']
                )->setComment('Vendor Notifications Table');
            $installer->getConnection()->createTable($table);
        }
        //phpcs:enable

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $tableName = $installer->getTable('ced_csmarketplace_vendor_products');
            $connection = $setup->getConnection();
            if ($installer->getConnection()->isTableExists($tableName) == true) {
                $connection->addColumn(
                    $tableName,
                    'reason',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => false,
                        'comment' => 'Disapproval Reason'
                    ],
                    null
                );
            }
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $tableName = $installer->getTable('ced_csmarketplace_vendor_sales_order');
            if ($installer->getConnection()->isTableExists($tableName)) {
                if ($installer->getConnection()->tableColumnExists($tableName, 'real_order_id') === false) {
                    $installer->getConnection()
                        ->addColumn(
                            $tableName,
                            'real_order_id',
                            [
                                'type' => Table::TYPE_INTEGER,
                                'unsigned' => true,
                                'nullable' => true,
                                'comment' => 'Sales order order_id'
                            ]
                        );
                }

                if ($installer->getConnection()->tableColumnExists($tableName, 'real_order_status') === false) {
                    $installer->getConnection()->addColumn(
                        $tableName,
                        'real_order_status',
                        [
                            'type' => Table::TYPE_TEXT,
                            'length' => '225',
                            'nullable' => true,
                            'comment' => 'Sales order order_status'
                        ]
                    );
                }
            }
        }


        if (version_compare($context->getVersion(), '2.0.7', '<')) {
            $this->createVendorUiBookmarkTable($setup);
        }

        $installer->endSetup();
    }


    public function createVendorUiBookmarkTable($setup){
        $connection = $setup->getConnection();
        $table = $connection->newTable($setup->getTable('vendor_ui_bookmark'))
            ->addColumn(
                'bookmark_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Vendor Bookmark identifier'
            )
            ->addColumn(
                'user_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'User Id'
            )
            ->addColumn(
                'namespace',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Vendor Bookmark namespace'
            )
            ->addColumn(
                'identifier',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Vendor Bookmark Identifier'
            )
            ->addColumn(
                'current',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false],
                'Mark current bookmark per user and identifier'
            )
            ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => true], 'Vendor Bookmark title')
            ->addColumn('config', Table::TYPE_TEXT, Table::MAX_TEXT_SIZE, ['nullable' => true], 'Vendor Bookmark config')
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['nullable' => false], 'Vendor Bookmark created at')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['nullable' => false], 'Vendor Bookmark updated at')
            ->addIndex(
                $setup->getIdxName(
                    'vendor_ui_bookmark',
                    ['user_id', 'namespace', 'identifier'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['user_id', 'namespace', 'identifier'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            )
            ->addForeignKey(
                $setup->getFkName('vendor_ui_bookmark', 'user_id', 'ced_csmarketplace_vendor', 'entity_id'),
                'user_id',
                $setup->getTable('ced_csmarketplace_vendor'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Vendor Bookmark');
        $connection->createTable($table);
    }
}
