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
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsStripePayment\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * Upgrade Data script
 *
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    public $_objectManager;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,EavSetupFactory $eavSetupFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavConfig = $this->_objectManager->create('Magento\Eav\Model\Config');
        $setup = $this->_objectManager->create('Magento\Framework\Setup\ModuleDataSetupInterface');

        $eavSetup->addAttribute(
            'customer_address',
            'ssn_number',
            [
                'type'         => 'varchar',
                'label'        => 'SSN Number',
                'input'        => 'text',
                'required'     => false,
                'visible'      => true,
                'user_defined' => true,
                'position'     => 999,
                'system'       => 0,
                'default' => 0000
            ]
        );
        $sampleAttribute = $eavConfig->getAttribute('customer_address',
            'ssn_number');

        $sampleAttribute->setData(
            'used_in_forms',
            [
                'adminhtml_customer_address','customer_address_edit','customer_register_address'

            ]

        );
        $sampleAttribute->save();
        $setup->endSetup();
    }
}
