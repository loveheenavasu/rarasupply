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

namespace Ced\CsRma\Controller;

use Magento\Framework\App\RequestInterface;

/**
 * Class Link
 * @package Ced\CsRma\Controller
 */
abstract class Link extends \Magento\Framework\App\Action\Action
{

    /**
     * @var  \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Ced\CsRma\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Link constructor.
     * @param \Ced\CsRma\Helper\Config $configHelper
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
     */
    public function __construct(
        \Ced\CsRma\Helper\Config $configHelper,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
    )
    {
        $this->_customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->configHelper = $configHelper;
        $this->_url = $context->getUrl();
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $helper = $this->configHelper;

        if ($helper->enableModule()) {
            if (!$this->_getSession()->authenticate()) {

                if ($helper->getAllowGuests()) {

                    return $this->resultRedirectFactory->create()->setPath('csrma/guestrma/form');

                } else {

                    $this->_actionFlag->set('', 'no-dispatch', true);
                }
            }
            return parent::dispatch($request);
        }
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _buildUrl($route = '', $params = [])
    {
        /** @var \Magento\Framework\UrlInterface $urlBuilder */
        return $this->_url->getUrl($route, $params);
    }
}