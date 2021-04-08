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
 
namespace Ced\Advertisement\Block\Adminhtml\Plan;
 
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

	 /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
	
	/**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
		\Magento\Framework\ObjectManagerInterface $objectInterface,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
		$this->_objectManager = $objectInterface;
        parent::__construct($context, $data);
    }

    public function _construct()
    {                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'Ced\Advertisement';
        $this->_controller = 'adminhtml_plan';
        		
		parent::_construct();
		
		$this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [ 'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']], ]
            ],
            10
        );
		
		$this->buttonList->remove('reset');
        $this->buttonList->update('save', 'label', __('Save Plan'));
		$this->buttonList->update('delete', 'label', __('Delete Plan'));
    }

	
	
	 /**
     * Getter for form header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $plan = $this->_coreRegistry->registry('plan_data');
        if ($plan->getId()) {
            return __('Edit Plan');
        } else {
            return __('Add Plan');
        }
    }

    /**
     * Retrieve products JSON
     *
     * @return string
     */
    public function getProductsJson()
    {
        return '{}';
    } 
}
