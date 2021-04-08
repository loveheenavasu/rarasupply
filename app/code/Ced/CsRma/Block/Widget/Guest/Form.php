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
namespace Ced\CsRma\Block\Widget\Guest;

class Form extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */

    protected $orderFactory;
   
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Ced\CsRma\Helper\Data $rmaDataHelper
     */

    public $rmaConfigHelper;
	
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Ced\Rma\Helper\Data
     */

    public $rmaDataHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Ced\Rma\Helper\Config $rmaConfigHelper
     * @param \Ced\Rma\Helper\Data $rmaDataHelper
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Api\DataObjectHelper
     * @param array $data
     */

    public function __construct(
       \Magento\Framework\View\Element\Template\Context $context,
        \Ced\CsRma\Helper\Config $rmaConfigHelper,
        \Ced\CsRma\Helper\Data $rmaDataHelper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        array $data = []
    ){   
    	$this->urlBuilder = $context->getUrlBuilder();
        $this->rmaDataHelper = $rmaDataHelper;
        $this->orderFactory = $orderFactory;
        $this->rmaConfigHelper = $rmaConfigHelper;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context,$data);
    }

    /**
     * Select element for choosing registry type
     *
     * @return array
     */
    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setData(
            ['id' => 'quick_search_type_id', 'class' => 'select guest-select']
        )->setName(
            'oar_email'
        )->setOptions(
            $this->_getFormOptions()
        )->setExtraParams(
            'onchange="showIdentifyBlock(this.value);"'
        );
        return $select->getHtml();
    }

    /**
     * Get Form Options for Guest
     *
     * @return array
     */
    protected function _getFormOptions()
    {
        $options = $this->getData('identifymeby_options');
        if ($options === null) {
            $options = [];
            $options[] = ['value' => 'email', 'label' => 'Email Address'];
            $this->setData('identifymeby_options', $options);
        }

        return $options;
    }

    /**
     * Get update button html
     *
     * @return string
     */
    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }
    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->urlBuilder->getUrl(
            'csrma/guestrma/save',
            ['_secure' => true]
        );
    }

}
