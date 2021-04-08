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
namespace Ced\CsCmsPage\Block\Cms\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('ced_cscms_page');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('CMS PAGE INFORMATION'));
         $this->setData('area','adminhtml');
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    { 
        $this->addTab(
            'main',
            [
                'label' => __('Page Information'),
                'title' => __('Page Information'),
                'content' => $this->getLayout()->createBlock('Ced\CsCmsPage\Block\Cms\Edit\Tab\Main')->toHtml(),
                'active' => true
            ]
        );
         $this->addTab(
            'content',
            [
                'label' => __('Content'),
                'title' => __('Content'),
                'content' => $this->getLayout()->createBlock('Ced\CsCmsPage\Block\Cms\Edit\Tab\Content')->toHtml(),
                'active' => false
            ]
        );
        $this->addTab(
            'design',
            [
                'label' => __('Design'),
                'title' => __('Design'),
                'content' => $this->getLayout()->createBlock('Ced\CsCmsPage\Block\Cms\Edit\Tab\Design')->toHtml(),
                'active' => false
            ]
        );
         $this->addTab(
            'meta_data',
            [
                'label' => __('Meta Data'),
                'title' => __('Meta Data'),
                'content' => $this->getLayout()->createBlock('Ced\CsCmsPage\Block\Cms\Edit\Tab\MetaData')->toHtml(),
                'active' => false
            ]
        );
        return parent::_beforeToHtml();
    }
}
