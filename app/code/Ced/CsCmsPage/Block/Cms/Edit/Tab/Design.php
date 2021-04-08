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
  * @category  Ced
  * @package   Ced_CsCmsPage
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsCmsPage\Block\Cms\Edit\Tab;

use Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Design extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
  protected $layout;
    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param PropertyLocker $propertyLocker
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        \Ced\CsCmsPage\Model\Source\Layout $layout,
        array $data = []
    ) {
        $this->layout = $layout;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _construct()
    {   
        $this->setData('area','adminhtml');
        parent::_construct();
    }

    /**
     * {@inheritdoc}
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var Attribute $attributeObject */
        $cmsPageData = $this->_coreRegistry->registry('current_cms_page');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'front_fieldset',
            ['legend' => __('Design'), 'collapsable' => $this->getRequest()->has('popup')]
        );
        $fieldset->addField(
            'layout',
            'select',
            [
                'label' => __('Layout'),
                'title' => __('Layout'),
                'name' => 'layout',
                'required' => true,
                'options' => $this->layout->getOptionArray()
            ]
        );
        $fieldset->addField(
            'default_layout',
            'checkbox',
            [
                'label' => __('Vendor Shop Page Default Layout'),
                'title' => __('Vendor Shop Page Default Layout'),
                'name' => 'default_layout'
            ]
        );
        $fieldset->addField(
            'layout_xml',
            'textarea',
            [
                'name' => 'layout_xml',
                'label' => __('Layout Update XML'),
                'title' => __('Layout Update XML'),
                'note' => 'Hint: Add Vendor cms by use of Layout XML e.g '.htmlentities('<referenceContainer name="content"><block class="Ced\CsCmsPage\Block\StaticBlock" name="xyz"><arguments><argument name="block_id" xsi:type="string">1</argument></arguments></block></referenceContainer>')
            ]
        );
        $form->setValues($cmsPageData);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Design');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Design');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}