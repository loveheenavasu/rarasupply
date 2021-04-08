<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRma\Block\Adminhtml\AllRma\Edit;

/**
 * Class Form
 * @package Ced\CsRma\Block\Adminhtml\AllRma\Edit
 */
class Form extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'edit/form.phtml';

    /**
     * @var \Ced\CsRma\Model\RequestFactory
     */
    public $rmaRequestFactory;

    /**
     * @var \Ced\CsRma\Helper\Config $rmaConfigHelper
     */
    public $rmaDataHelper;

    /**
     * @var \Ced\CsRma\Model\RmaitemsFactory
     */
    protected $rmaItemFactory;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesno;

    /**
     * Form constructor.
     * @param \Ced\CsRma\Model\RmaitemsFactory $rmaItemFactory
     * @param \Ced\CsRma\Model\RequestFactory $rmaRequestFactory
     * @param \Ced\CsRma\Helper\Data $rmaDataHelper
     * @param \Magento\Config\Model\Config\Source\Yesno $yesno
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Ced\CsRma\Model\RmaitemsFactory $rmaItemFactory,
        \Ced\CsRma\Model\RequestFactory $rmaRequestFactory,
        \Ced\CsRma\Helper\Data $rmaDataHelper,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->rmaItemFactory = $rmaItemFactory;
        $this->rmaRequestFactory = $rmaRequestFactory;
        $this->rmaDataHelper = $rmaDataHelper;
        $this->yesno = $yesno;
        parent::__construct($context, $data);
    }

    /**
     * Preparing global layout
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'ced_csrma_chat',
            'Ced\CsRma\Block\Adminhtml\AllRma\Chat'
        );
        return parent::_prepareLayout();
    }

    /**
     * Return the Url for saving.
     * @return string
     */
    public function getEditUrl()
    {
        return $this->_urlBuilder->getUrl(
            'csrma/allRma/save', ['_secure' => true]
        );
    }

    /**
     * Return rma request table collection.
     * @return array
     */
    public function getRequestCollection()
    {
        $request_collect = $this->rmaRequestFactory->create()->load($this->getRequest()->getParam('id'));
        return $request_collect;
    }

    /**
     * @return array
     */
    public function getOrderData()
    {
        return $this->rmaDataHelper->getOrderCollection($this->getRequestCollection()->getOrderId());
    }

    /**
     * Return rma request order-item collection.
     *
     * @return array
     */
    public function getItemCollection()
    {
        $item_collect = $this->rmaItemFactory->create()
            ->getCollection()
            ->addFieldToFilter('rma_request_id',
                $this->getRequest()->getParam('id'));
        return $item_collect;
    }

    /**
     * @return array
     */
    public function getYesNo()
    {
        $yesnoSource = $this->yesno
            ->toOptionArray();
        return $yesnoSource;
    }
}
    