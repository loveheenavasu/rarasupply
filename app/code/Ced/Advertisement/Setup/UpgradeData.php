<?php

namespace Ced\Advertisement\Setup;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

/*
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    public function __construct(        
        EavSetupFactory $eavSetupFactory)
    {       
        $this->eavSetupFactory = $eavSetupFactory;        
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]); 
        $catalogEntityType = $eavSetup->getEntityTypeId('catalog_product');

        if (version_compare($context->getVersion(), '0.0.2', '<')) {
		    $connection->rawQuery("INSERT INTO `".$setup->getTable('ced_advertisement_positions')."` (`position_name`, `identifier`, `position_status`) VALUES
		        ('Show Ad in header', 'show_ad_in_header', 
		        '0'),('Show Ad in footer', 'show_ad_in_footer', '0'),('Show Ad in Upper Left Sidebar', 'show_ad_in_upper_left_sidebar', '0'),('Show Ad in Lower Left Sidebar', 'show_ad_in_lower_left_sidebar', '0'),('Show Ad in Main Content Top', 'show_ad_in_main_content_top', '0'),('Show Ad in Main Content Bottom', 'show_ad_in_main_content_bottom', '0'),('Show Ad in Product Info', 'show_ad_in_product_info', '0'),('Show Ad in Checkout', 'show_ad_in_checkout', '0');
		    ");
		}

        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'position_identifier',
                [
                    'type' => 'varchar',
                    'label' => 'Position Identifier',
                    'input' => 'select',
                    'source'=>'Ced\Advertisement\Model\Source\Position',
                    'required' => false,
                    'visible' => true,
                    'system' => 0,
                    'is_user_defined' => 1,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'default' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'apply_to' => ''
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.7', '<')) {            
            $eavSetup->updateAttribute($catalogEntityType,'position_identifier','is_visible',false);
            $eavSetup->updateAttribute($catalogEntityType,'duration','is_visible',false);
        }
        
		$setup->endSetup();
    }
}
