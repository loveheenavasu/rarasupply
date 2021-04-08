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
 * @author   	 CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
	private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory)
	{
       
		$this->eavSetupFactory = $eavSetupFactory;
	}
	
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        /**
         *
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY, 'ced_rpoint', [
            'group'=> 'General',
            'type'=>'int',
            'backend'=>'',
            'frontend'=>'',
            'label'=>'rewardpoint',
            'input'=>'text',
            'class'=>'',
            'source'=>'',
            'global'=>\Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
            'visible'=>true,
            'required'=>false,
            'user_defined'=>true,
            'searchable'=>false,
            'filterable'=>false,
            'comparable'=>false,
            'visible_on_front'=>false,
	        'visible_in_advanced_search' => false,
            'used_in_product_listing'=>true,
        	'is_used_in_grid' =>true,
            'unique'=>false,
            'apply_to'=>'simple,downloadable,virtual,configurable'  // Apply to simple product type
        ]
        );
        $eavSetup->addAttributeToSet ( 'catalog_product', 'Default', 'General', 'ced_rpoint');
        
        
	}
}
