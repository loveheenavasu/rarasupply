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

namespace Ced\CsCmsPage\Block\Adminhtml;
class Cmspage extends \Magento\Backend\Block\Widget\Grid\Container
{

    //protected $_template = 'Ced_CsCmsPage::cms.phtml';
    //protected $pageLayoutBuilder;

    /* public function __construct(
            \Magento\Backend\Block\Widget\Context $context,
            \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
            array $data = []
    ) {
        $this->_controller = 'adminhtml_cmspage';
        $this->_blockGroup = 'Ced_CsCmsPage';
        $this->_headerText = ('Manage Cms Pages');
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        parent::__construct($context, $data);
    } */

    protected function _construct()
    {
        $this->_controller = 'adminhtml_cmspage';
        $this->_blockGroup = 'Ced_CsCmsPage';
        $this->_headerText = __('Manage Cms Pages');

        parent::_construct();

        if ($this->_isAllowedAction('Magento_Cms::save')) {
            $this->buttonList->remove('add', 'label', __('Add New Page'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

}
