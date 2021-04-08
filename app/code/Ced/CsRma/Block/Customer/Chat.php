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
namespace Ced\CsRma\Block\Customer;

class Chat extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ced\CsRma\Helper\Config
     */
    public $rmaConfigHelper;

    /**
     * @var \Ced\Rma\Model\RmachatFactory
     */
    public $rmaChatFactory;
	
    /**
     * @var string
     */
    protected $_template = 'customer/chat.phtml';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Constructor
     *
     * @param \Ced\Rma\Model\RmachatFactory $rmaChatFactory
     * @param \Ced\Rma\Helper\Config $rmaConfigHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */

    public function __construct(
        \Ced\CsRma\Model\RmachatFactory $rmaChatFactory,
        \Ced\CsRma\Helper\Config $rmaConfigHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ){ 
        $this->rmaConfigHelper = $rmaConfigHelper;
        $this->rmaChatFactory = $rmaChatFactory;
        parent::__construct($context,$data);
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('csrma/*/saveChat',['id'=>$this->getRequest()
                        ->getParam('id')]);
    }
}
