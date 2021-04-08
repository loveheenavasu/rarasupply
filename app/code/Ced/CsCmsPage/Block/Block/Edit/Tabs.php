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
namespace Ced\CsCmsPage\Block\Block\Edit;

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
        $this->setTitle(__('CMS BLOCK INFORMATION'));
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
                'label' => __('Genereal Information'),
                'title' => __('Genereal Information'),
                'content' => $this->getLayout()->createBlock('Ced\CsCmsPage\Block\Block\Edit\Tab\Main')->toHtml(),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
