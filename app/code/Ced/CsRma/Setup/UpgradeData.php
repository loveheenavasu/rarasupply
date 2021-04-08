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

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
       
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
          
          /**
             * Add attribute_code in component attribute table
             */
            $setup->getConnection()->insertForce(
                $setup->getTable('ced_csrma_status'),
                ['status'=>'Pending','sortOrder'=>1,'active'=>1,'notification'=>'Pending'] );

            $setup->getConnection()->insertForce(
                $setup->getTable('ced_csrma_status'),
                ['status'=>'Approved','sortOrder'=>2,'active'=>1,'notification'=>'Approved'] );

            $setup->getConnection()->insertForce(
                $setup->getTable('ced_csrma_status'),
                ['status'=>'Completed','sortOrder'=>3,'active'=>1,'notification'=>'Completed'] );

            $setup->getConnection()->insertForce(
                $setup->getTable('ced_csrma_status'),
                ['status'=>'Cancelled','sortOrder'=>4,'active'=>1,'notification'=>'Cancelled'] );
        } 
        $setup->endSetup();
    }
}
