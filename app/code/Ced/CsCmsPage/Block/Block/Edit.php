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
  * @category   Ced
  * @package    Ced_CsCmsPage
  * @author     CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright  Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license    http://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsCmsPage\Block\Block;

/**
 * Product attribute edit page
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Block group name
     *
     * @var string
     */
    protected $_blockGroup = 'Ced_CsCmsPage';

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
        array $data = []
    ) {
      
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->setData('area','adminhtml');
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'page_id';
        $this->_controller = 'block';

        parent::_construct();

        if ($this->getRequest()->getParam('popup')) {
            $this->buttonList->remove('back');
            if ($this->getRequest()->getParam('product_tab') != 'variations') {
                $this->addButton(
                    'save_in_new_set',
                    [
                        'label' => __('Save in New Attribute Set'),
                        'class' => 'save',
                        'onclick' => 'saveAttributeInNewSet(\'' . __('Enter Name for New Attribute Set') . '\')',
                        'area' => 'adminhtml'
                    ],
                    100
                );
            }
        } else {
            $this->addButton(
                'save_and_edit_button',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ],
                    'area' => 'adminhtml'
                ]
            );
        }

        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
                'class' => 'back',
                'area' => 'adminhtml'
            ],
            -1
        );
        if ($block_id = $this->getRequest()->getParam('block_id')) {
            $this->addButton(
                'delete',
                [
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getUrl('cscmspage/vblock/delete', ['block_id' => $block_id]) . '\')',
                    'area' => 'adminhtml'
                ]
            );
        }

        $this->addButton(
            'save',
            [
                'label' => __('Save Block'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                ],
                'area' => 'adminhtml'
            ],
            1
        );
        $this->removeButton('save_and_edit_button');
        $this->removeButton('reset');
        $this->setData('area','adminhtml');
    }

    /**
     * {@inheritdoc}
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        if ($this->getRequest()->getParam('popup')) {
            $region = 'header';
        }
        parent::addButton($buttonId, $data, $level, $sortOrder, $region);
    }

    /**
     * Retrieve header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('entity_attribute')->getId()) {
            $frontendLabel = $this->_coreRegistry->registry('entity_attribute')->getFrontendLabel();
            if (is_array($frontendLabel)) {
                $frontendLabel = $frontendLabel[0];
            }
            return __('Edit Product Attribute "%1"', $this->escapeHtml($frontendLabel));
        }
        return __('New Product Attribute');
    }

    /**
     * Retrieve URL for save
     *
     * @return string
     */
    public function getSaveUrl()
    {
        $action = $this->getUrl('cscmspage/vblock/saveblock', ['_current' => true]);
        if ($block_id = $this->getRequest()->getParam('block_id')) {
            $action = $this->getUrl('cscmspage/vblock/updateblock', ['block_id' => $block_id, '_current' => true]);
        }
        return $action;
    }

    public function getBackUrl()
    {
      return $this->getUrl('cscmspage/vblock/index');
    }
}
