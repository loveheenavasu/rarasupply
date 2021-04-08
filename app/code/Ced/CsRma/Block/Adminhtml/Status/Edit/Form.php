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
namespace Ced\CsRma\Block\Adminhtml\Status\Edit;

/**
 * Adminhtml cms block edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('grid_form');
        $this->setTitle(__('Block Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('ced_csrma_status');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('status_');
        $isElementDisabled = false;

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getStatusId()) {
            $fieldset->addField('status_id', 'hidden', ['name' => 'status_id']);
        }

        $fieldset->addField(
            'status',
            'text',
            ['name' => 'status', 'label' => __('Status Label'), 'title' => __('Status Label'), 'required' => true]
        );

        $fieldset->addField(
            'sortOrder',
            'text',
            [
                'name' => 'sortOrder',
                'label' => __('Status Sort Order'),
                'title' => __('Status Sort Order'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'active',
            'select',
            [
                'label' => __('Enable'),
                'title' => __('Enable'),
                'name' => 'active',
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );

        $fieldset->addField(
            'notify_email',
            'select',
            [
                'name' => 'notify_email',
                'label' => __('Notify Customer by Email '),
                'title' => __('Notify Customer by Email '),
                'required' => true,
                'options' => ['Yes' => __('Yes'), 'No' => __('No')]
            ]
        );
        
        $fieldset->addField(
            'frontend_visible',
            'select',
            [
                'name' => 'frontend_visible',
                'label' => __('Visible on Storefront'),
                'title' => __('Visible on Storefront'),
                'required' => true,
                'options' => ['Yes' => __('Yes'), 'No' => __('No')]
            ]
        );

        $fieldset->addField(
            'notification',
            'editor',
            [
                'name' => 'notification',
                'label' => __('Notification Message'),
                'title' => __('Notification Message'),
                'style' => 'height:20em',
                'required' => true,
                'disabled' => $isElementDisabled
               
            ]
        );

        if (!$model->getStatusId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
