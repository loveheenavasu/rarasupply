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

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as DataForm;

class Form extends Generic
{
    /**
     * set area adminhtml
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setData('area','adminhtml');
    }

    protected function _prepareForm()
    {
        $action = $this->getUrl('cscmspage/vcmspage/savecms');
        if ($pageId = $this->getRequest()->getParam('page_id')) {
            $action = $this->getUrl('cscmspage/vcmspage/updatecms', ['page_id' => $pageId]);
        }
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $action, 'method' => 'post']]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
