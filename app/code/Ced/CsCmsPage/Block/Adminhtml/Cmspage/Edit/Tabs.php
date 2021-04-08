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
  * @package     Ced_CsCmsPage
  * @author   CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsCmsPage\Block\Adminhtml\Cmspage\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
    	
        parent::_construct();
        $this->setId('cmspage_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('CmsPage Information'));
    }
    protected function _beforeToHtml()
    {
    	$this->addTab(
    			'main',
    			[
    			'label' => __('Page Information'),
    			'title' => __('Page Information'),
    			'content' => $this->getLayout()->createBlock('Ced\CsCmsPage\Block\Adminhtml\Cmspage\Edit\Tab\Main')->toHtml(),
    			'active' => true
    			]
    	);
    	
    	$this->addTab(
    			'Content',
    			[
    			'label' => __('Content'),
    			'title' => __('Content'),
    			'content' => $this->getLayout()->createBlock('Ced\CsCmsPage\Block\Adminhtml\Cmspage\Edit\Tab\Content')->toHtml(),
    			'active' => false
    			]
    	);
    	$this->addTab(
    			'Design',
    			[
    			'label' => __('Design'),
    			'title' => __('Design'),
    			'content' => $this->getLayout()->createBlock('Ced\CsCmsPage\Block\Adminhtml\Cmspage\Edit\Tab\Design')->toHtml(),
    			'active' => false
    			]
    	);
    	$this->addTab(
    			'Meta',
    			[
    			'label' => __('Meta Deta'),
    			'title' => __('Meta Deta'),
    			'content' => $this->getLayout()->createBlock('Ced\CsCmsPage\Block\Adminhtml\Cmspage\Edit\Tab\Meta')->toHtml(),
    			'active' => false
    			]
    	);
    	
    
    	return parent::_beforeToHtml();
    }
}

