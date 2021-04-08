<?php

namespace Ced\Advertisement\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Init
     * @param CustomerSetupFactory $customerSetupFactory
     * @param \Magento\Customer\SetupCustomerSetupFactory $customerSetupFactory
     */
    public function __construct(        
        EavSetupFactory $eavSetupFactory,
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
    ){       
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var ContactSetup $customerSetup */
       
        $setup->startSetup();  
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);     
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'is_plan',
            [
                'type' => 'int',
                'label' => 'Is Plan',
                'input' => 'hidden',
                'required' => false,
                'visible' => false,
                'system' => 0,
                'is_user_defined' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'default' => 0,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to' => ''
            ]
        );

        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'duration',
                [
                    'type' => 'int',
                    'label' => 'Duration',
                    'input' => 'text',
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
        $setup->endSetup();
    }
}