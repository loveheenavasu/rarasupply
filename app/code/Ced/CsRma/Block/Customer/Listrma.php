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

class Listrma extends \Magento\Framework\View\Element\Template
{
	
	/**
     * Collection of RMA Requests
     *
     * @var \Ced\CsRma\Model\Resource\Request\Collection
     */
    private $_rmaRequestCollection = null;

    /**
     * @var \Ced\CsRma\Model\Resource\Request\CollectionFactory
     */
    protected $rmaResourceFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \Ced\CsRma\Model\Resource\Rmachat\CollectionFactory
     */
    protected $rmaResourceRmachatCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Ced\Rma\Model\RequestFactory $rmaResourceFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     *
     */

    public function __construct(
    	
        \Magento\Framework\View\Element\Template\Context $context,
        \Ced\CsRma\Model\RequestFactory $rmaResourceFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {   
    	$this->urlBuilder = $context->getUrlBuilder();
        $this->customerSession = $customerSession;
        $this->rmaResourceFactory = $rmaResourceFactory;
        parent::__construct($context,$data);
    }

    /**
     * @return $this
     */
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Return Merchandise List'));
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getRmaRequestData()
    {
        $limit = $this->getRequest()->getParam('limit');
        if(!$limit){
            $limit = 10;
        }
        $_rmaRequestCollection = $this->rmaResourceFactory->create()
            ->getCollection()->setPageSize($limit)
            ->addFieldtoFilter('customer_id', $this->customerSession
                ->getCustomerId())
            ->setOrder('created_at', 'DESC')
            ;
            
        return $_rmaRequestCollection;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $limit = $this->getRequest()->getParam('limit');
        if(!$limit){
            $limit = 10;
        }
        if ($this->getRmaRequestData()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'rma.request.history.pager'
            )->setLimit($limit)->setCollection(
                $this->getRmaRequestData()
            );
            $this->setChild('pager', $pager);
            $this->getRmaRequestData()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($request)
    {
        return $this->getUrl('csrma/customerrma/view', ['id' => $request['rma_request_id']]);
    }

    /**
     * @return string
     */
    public function getNewRmaUrl()
    {
        return $this->getUrl('csrma/customerrma/new', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Format date in short format
     *
     * @param string $date
     * @return string
     */
    public function dateFormat($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::SHORT);
    }


}

