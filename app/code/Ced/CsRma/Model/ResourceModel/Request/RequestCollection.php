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

namespace Ced\CsRma\Model\ResourceModel\Request;

/**
 * Class RequestCollection
 * @package Ced\CsRma\Model\ResourceModel\Request
 */
class RequestCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $backendUrl;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Request Collection Resource Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ced\CsRma\Model\Request', 'Ced\CsRma\Model\ResourceModel\Request');
    }

    /**
     * RequestCollection constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Backend\Model\Url $backendUrl
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Backend\Model\Url $backendUrl,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_request = $request;
        $this->redirect = $redirect;
        $this->backendUrl = $backendUrl;
        $this->orderFactory = $orderFactory;
    }

    protected function _renderFiltersBefore()
    {
        $orderId = $this->_request->getParam('order_id', false);
        $url = $this->redirect->getRefererUrl();
        $index = strpos($url, '/sales/order/view/order_id/');
        if ($index !== false) {
            $baseUrl = $this->backendUrl->getBaseUrl();
            $request_uri = trim(substr($url, strlen($baseUrl)), '/');
            $request_uri = explode('/', $request_uri);
            $orderId = false;
            foreach ($request_uri as $key => $value) {
                if ($value == 'order_id') {
                    $orderId = $request_uri[$key + 1];
                    break;
                }
            }
            if ($orderId) {
                $salesModel = $this->orderFactory->create()->load($orderId);
                if ($salesModel && $salesModel->getId())
                    $this->addFieldToFilter('order_id', $salesModel->getIncrementId());
            }
        }
    }
}