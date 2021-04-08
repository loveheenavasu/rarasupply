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
 * @package     Ced_CsStripePayment
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsStripePayment\Setup;
 
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
    	$installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '0.0.3') <= 0) {
            $wtableName = $installer->getTable('ced_csstripe_managed_acc');
            if ($installer->getConnection()->isTableExists($wtableName) == true) {
                $connection = $setup->getConnection();
                $connection->addColumn(
                    $wtableName,
                    'status',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'length' => null,
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'Status'
                    ]
                );
            }  

        }
 
        $installer->endSetup();
    }
}