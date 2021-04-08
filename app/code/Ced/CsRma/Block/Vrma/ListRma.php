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

namespace Ced\CsRma\Block\Vrma;

use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ListRma
 * @package Ced\CsRma\Block\Vrma
 */
class ListRma extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var \Ced\CsRma\Model\ResourceModel\Request\CollectionFactory
     */
    protected $requestcollectionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    protected $storeRepository;

    /**
     * ListRma constructor.
     * @param \Ced\CsRma\Model\ResourceModel\Request\CollectionFactory $requestcollectionFactory
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param \Ced\CsMarketplace\Model\Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Ced\CsRma\Model\ResourceModel\Request\CollectionFactory $requestcollectionFactory,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        \Ced\CsMarketplace\Model\Session $customerSession,
        UrlFactory $urlFactory
    )
    {
        $this->requestcollectionFactory = $requestcollectionFactory;
        $this->storeRepository = $storeRepository;

        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

        $vendorId = $this->getVendorId();
        $requestCollection = $this->requestcollectionFactory->create()
            ->addFieldToFilter('vendor_id', $vendorId);
        $filterCollection = $this->filterRma($requestCollection);
        $this->setVrma($requestCollection);
    }

    /**
     * @param $requestCollection
     * @return mixed
     */
    public function filterRma($requestCollection)
    {
        $params = $this->session->getData('rma_filter');
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $field => $value) {
                if (is_array($value)) {

                    if (isset($value['from']) && urldecode($value['from']) != "") {
                        $from = urldecode($value['from']);
                        if ($field == 'updated_at') {
                            $from = date("Y-m-d 00:00:00", strtotime($from));
                        }
                        $requestCollection->addFieldToFilter($field, array('gteq' => $from));
                    }
                    if (isset($value['to']) && urldecode($value['to']) != "") {

                        $to = urldecode($value['to']);

                        if ($field == 'updated_at') {
                            $to = date("Y-m-d 59:59:59", strtotime($to));
                        }
                        $requestCollection->addFieldToFilter($field, array('lteq' => $to));
                    }
                } else if (urldecode($value) != "") {
                    $requestCollection->addFieldToFilter($field, array("like" => '%' . urldecode($value) . '%'));
                }
            }
        }
        return $requestCollection;
    }

    /**
     * prepare list layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'custom.pager');
        $pager->setAvailableLimit(array(5 => 5, 10 => 10, 20 => 20, 'all' => 'all'));
        $pager->setCollection($this->getVrma());
        $this->setChild('pager', $pager);
        return $this;
    }

    /**
     * return the pager
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * return Back Url
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index', array('_secure' => true, '_nosid' => true));
    }

    /**
     * Return order view link
     *
     * @param string $order
     * @return String
     */
    public function getEditUrl($rma)
    {
        return $this->getUrl('*/*/edit', array('rma_id' => $rma->getRmaRequestId(), '_secure' => true, '_nosid' => true));
    }

    /**
     * @param $storeId
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreValue($storeId)
    {
        $storeModel = $this->storeRepository->getById($storeId);
        return $storeModel->getName();
    }
}
