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

 
namespace Ced\Advertisement\Block\Adminhtml\Plan\Edit;
 
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
 	protected $_directoryHelper;
     
    public function __construct(
    		\Magento\Backend\Block\Template\Context $context,
    		\Magento\Framework\Registry $registry,
    		\Magento\Framework\Data\FormFactory $formFactory,
    		\Magento\Directory\Helper\Data $directoryHelper,
    		array $data = []
    ) {
    	parent::__construct($context, $registry,$formFactory,$data);
    	$this->_directoryHelper = $directoryHelper;
    	$this->_coreRegistry = $registry;
    }
    
	protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_form');
        $this->setTitle(__('Plan Information'));
    }

	protected function _prepareForm()
	{
		$form = $this->_formFactory->create([
						'data' => [
                                'id' => 'edit_form',
                                'action' => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
                                'method' => 'post',
        						'enctype' => 'multipart/form-data',
                        ],
					]	
				);

		$form->setUseContainer(true);
		$this->setForm($form);
		return parent::_prepareForm();
	}
}
