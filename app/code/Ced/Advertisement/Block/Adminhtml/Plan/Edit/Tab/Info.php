<?php 

namespace Ced\Advertisement\Block\Adminhtml\Plan\Edit\Tab;
 
class Info extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
   
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
    public $_objectManager;
    protected $_status;
    protected $_grid1Factory;
    protected $_countryFactory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Ced\Advertisement\Model\Source\Position\Identifier $identifier,
        \Ced\Advertisement\Model\Source\Plan\Status $status,
        array $data = []
    ) {
        $this->_status = $status;
        $this->_position = $identifier;
        parent::__construct($context, $registry, $formFactory, $data);
    }
 
    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('plan_data');
        $form = $this->_formFactory->create();
 
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Plan Information')]);
        if ($model->getId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'id']);
        }
 
        $fieldset->addField(
            'status',
            'select',
            [
                'name'  => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'options' => $this->_status->toOptionArray()
            ]
        );
        
        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Plan Name'),
                'title' => __('Plan Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'price',
            'text',
            [
                'name' => 'price',
                'class' => 'validate-number validate-greater-than-zero',
                'label' => __('Price'),
                'title' => __('Price'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'qty',
            'text',
            [
                'name' => 'qty',
                'class' => 'validate-number validate-greater-than-zero',
                'label' => __('Quantity'),
                'title' => __('Quantity'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'duration',
            'text',
            [
                'name' => 'duration',
                'class' => 'validate-number validate-greater-than-zero',
                'label' => __('Duration'),
                'title' => __('Duration'),
                'required' => true,
                'note' => __('set in days format')
            ]
        );
        
        $fieldset->addField(
            'position_identifier',
            'select',
            [
                'name' => 'position_identifier',
                'label' => __('Position Identifier'),
                'title' => __('Position Identifier'),
                'options' => $this->_position->toOptionArray(),
                'required' => true
            ]
        );
        if($model && $model->getId()){
            $qty = $model->getExtensionAttributes()->getStockItem()->getQty();
            $model->setQty($qty);    
        }
        
        $form->setValues($model->getData());
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
        return __('General Information Settings');
    }
 
    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Generel Information Settings');
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
 
    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}


