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
 * @package     Ced_CsDeal
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Setup;
 
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
		$installer = $setup; 
        $installer->startSetup();

        /**
         * Create table 'ced_csdeal_dealsetting'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csdeal_dealsetting')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Vendor Id'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Status'
        )->addColumn(
            'deal_list',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Deal List'
        )->addColumn(
            'timer_list',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Timer List'
        )->addColumn(
            'deal_message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['unsigned' => true],
            'Deal Message'
        )->addColumn(
            'store',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Store'
        )->setComment(
            'Vendor Deal Settings Table'
        );

        $installer->getConnection()->createTable($table);
        
        /**
         * Create table 'ced_csdeal_deal'
         */
        $table = $installer->getConnection()->newTable(
        		$installer->getTable('ced_csdeal_deal')
        )->addColumn(
        		'deal_id',
        		\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        		null,
        		['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        		'Deal Id'
        )->addColumn(
        		'product_id',
        		\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        		null,
        		['unsigned' => true, 'nullable' => false],
        		'Product Id'
        )->addColumn(
        		'days',
        		\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        		null,
        		['unsigned' => true, 'nullable' => false],
        		'Days'
        )->addColumn(
        		'specificdays',
        		\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        		null,
        		['nullable' => false],
        		'Specific Days'
        )->addColumn(
        		'start_date',
        		\Magento\Framework\DB\Ddl\Table::TYPE_DATE,
        		null,
        		['unsigned' => true, 'nullable' => false],
        		'Start Date'
        )->addColumn(
        		'end_date',
        		\Magento\Framework\DB\Ddl\Table::TYPE_DATE,
        		null,
        		['unsigned' => true, 'nullable' => false],
        		'End Date'
        )->addColumn(
        		'vendor_id',
        		\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        		null,
        		['unsigned' => true, 'nullable' => false],
        		'Vendor Id'
        )->addColumn(
        		'admin_status',
        		\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        		null,
        		['unsigned' => true],
        		'Admin Status'
        )->addColumn(
        		'status',
        		\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        		null,
        		['unsigned' => true],
        		'Status'
        )->addColumn(
        		'deal_price',
        		\Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        		'10,2',
        		['unsigned' => true],
        		'Deal Price'
        )->setComment(
        		'Vendor Deal Table'
        );
        
        $installer->getConnection()->createTable($table);

        $installer->endSetup(); 
    }
}