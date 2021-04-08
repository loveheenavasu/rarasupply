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
 
namespace Ced\Advertisement\Block\Adminhtml;

class Plan extends \Magento\Backend\Block\Widget\Container
{
	 /**
     * @var string
     */
    protected $_template = 'position/position.phtml';
 
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->getAddButtonOptions();
    }
 
    /**
     *
     *
     * @return array
     */
    protected function getAddButtonOptions()
    {
        $splitButtonOptions = [
            'label' => __('Add New Plan'),
            'class' => 'primary',
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];
        $this->buttonList->add('add', $splitButtonOptions);
        return $this;
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl('*/*/new' );
    }

    /**
     * Prepare button and grid
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Ced\Advertisement\Block\Adminhtml\Plan\Grid', 'ced.advertisement.plan.grid')
        );
        return parent::_prepareLayout();
    }
 
 
    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    { 
        return $this->getChildHtml('grid');
    }
}
