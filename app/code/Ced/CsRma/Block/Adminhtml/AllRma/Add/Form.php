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
namespace Ced\CsRma\Block\Adminhtml\AllRma\Add;

class Form extends \Magento\Backend\Block\Template
{

    /**
     * @var string
     */
    protected $_template = 'add/form.phtml';

    /**
     * @var \Ced\CsRma\Helper\Config $rmaConfigHelper
     */
    
    public $rmaConfigHelper;
    
    /**
    * @var \Ced\Rma\Model\RmastatusFactory
    */

    protected $rmaStatusFactory;
    /**
     * @var \Ced\CsRma\Helper\OrderDetail
     */
    public $rmaOrderHelper;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Ced\CsRma\Helper\Config $rmaConfigHelper
     * @param \Ced\CsRma\Model\RmastatusFactory $rmaStatusFactory
     * @param array $data
     */
    public function __construct(
        \Ced\CsRma\Helper\Config $rmaConfigHelper,
        \Ced\CsRma\Model\RmastatusFactory $rmaStatusFactory,
        \Ced\CsRma\Helper\OrderDetail $rmaOrderHelper,
        \Magento\Backend\Block\Template\Context $context, 
        array $data = []
    ) {
        $this->rmaOrderHelper = $rmaOrderHelper;
        $this->rmaStatusFactory = $rmaStatusFactory;
        $this->rmaConfigHelper = $rmaConfigHelper;
        parent::__construct($context,$data);
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'csrma/allrma/adminRma',
            ['_secure' => true]
        );
    }
    
    /**
     * Return the status collection.
     *
     * @return array
     */
    public function getStatusCollection()
    {
        $status_collect = $this->rmaStatusFactory->create()
            ->getCollection()
            ->addFieldToFilter('status',array('neq'=>'Approved'));
        return $status_collect;
    }

}
