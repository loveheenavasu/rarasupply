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
namespace Ced\CsRma\Block\Adminhtml\AllRma;
 
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
    * @var \Ced\CsRma\Model\RmastatusFactory
    */
    protected $rmaStatusFactory;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
 
    /**
     * @param \Ced\Rma\Model\RmastatusFactory $rmaStatusFactory
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Ced\CsRma\Model\RmastatusFactory $rmaStatusFactory,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->rmaStatusFactory = $rmaStatusFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
 
    /**
     * Initialize blog post edit block
     *
     * @return void
     */
    protected function _construct()
    {
        
    	$this->_objectId = 'id';
        $this->_blockGroup = 'Ced_CsRma';
        $this->_controller = 'adminhtml_allRma';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Request'));
        $this->buttonList->remove('delete');
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        ); 
        

    }

    /**
     * Retrieve rma model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getRmaRequest()
    {
       return $this->_coreRegistry->registry('ced_csrma_request');
    }

    /**
     * Retrieve rma Identifier
     *
     * @return int
     */
    public function getRmaId()
    {
        return $this->getRmaRequest() ? $this->getRmaRequest()
                ->getRmaRequestId() : null;
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('csrma/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }
    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'content');
                }
            };
        ";
        return parent::_prepareLayout();
    }
}