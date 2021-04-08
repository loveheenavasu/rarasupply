<?php 
namespace Ced\Rewardsystem\Setup;
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
 * @package     Ced_Rewardsystem
 * @author   	CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */ 
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
       
        $setup->startSetup();
        
        $table = $setup->getConnection()->newTable(
            $setup->getTable('ced_point')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Custom Id'
        )->addColumn(
            'prduct_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Product Id'
        )->addColumn(
            'point',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Point'
        )->setComment(
            'Custom Table'
        );
        $setup->getConnection()->createTable($table);

              /**
         * Create table 'catalogrule'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('ced_rewardrule'))
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Name'
            )
            ->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Description'
            )
            ->addColumn(
                'from_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'From'
            )
            ->addColumn(
                'to_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'To'
            )
            ->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Is Active'
            )
            ->addColumn(
                'conditions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Conditions Serialized'
            )
            ->addColumn(
                'actions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Actions Serialized'
            )
            ->addColumn(
                'stop_rules_processing',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Stop Rules Processing'
            )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )
            ->addColumn(
                'simple_condition',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Simple Condition'
            )
            ->addColumn(
                'point_x',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true,'nullable' => false, 'default' => '0'],
                'Point X'
            )
             ->addColumn(
                'moneystep',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true,'nullable' => false, 'default' => '0'],
                'Money Step(y)'
            )
               ->addColumn(
                'qtystep',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true,'nullable' => false, 'default' => '0'],
                'Qty Step(y)'
            )
                ->addColumn(
                'max_point',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true,'nullable' => false, 'default' => '0'],
                'Maximut Point'
            )
            ->addColumn(
                'moneymax_point',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true,'nullable' => false, 'default' => '0'],
                'Maximut Point'
            )
            ->addColumn(
                'sub_is_enable',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Is Rule Enable For Subitems'
            )
            ->addColumn(
                'sub_simple_action',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                [],
                'Simple Action For Subitems'
            )
            ->addColumn(
                'check_point_in',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true,'nullable' => false, 'default' => '0'],
                'Discount Amount For Subitems'
            )
          
            ->setComment('RewardRule');
             $setup->getConnection()->createTable($table);
            
             /*table for registered user*/
            

            $table = $setup->getConnection()->newTable(
            $setup->getTable('ced_regisuserpoint')
            )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Custom Id'
            )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Customer Id'
            )->addColumn(
            'point',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Point'
            )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Title'
             )->addColumn(
                'creating_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Creating Date'
            )->addColumn(
                'expiration_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Expiration Date'
            )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Status'
            )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'order_id'
            )->addColumn(
            'point_used',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'point_used'
            )->setComment(
            'Custom Table'
        );
        $setup->getConnection()->createTable($table);
       
     

        $setup->getConnection()->addColumn(
        		$setup->getTable('sales_order'),
        		'rewardsystem_base_amount',
        		[
        		'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        		'length'    => '10,4',
        		'nullable' => false,
        		'comment' => 'Reward Base Amount'
        		]
        );
        
        $setup->getConnection()->addColumn(
        		$setup->getTable('sales_order'),
        		'rewardsystem_discount',
        		[
        		'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        		'length'    => '10,4',
        		'nullable' => false,
        		'comment' => 'Reward Discount'
        		]
        );
        

 
        $setup->endSetup();
}
}