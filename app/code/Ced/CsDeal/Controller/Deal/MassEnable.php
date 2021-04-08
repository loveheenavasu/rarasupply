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
 * @package     Ced_CsDeal
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Controller\Deal;

use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\App\Action\Context;

/**
 * Class MassEnable
 * @package Ced\CsDeal\Controller\Deal
 */
class MassEnable extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsDeal\Model\DealFactory
     */
    protected $dealFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * MassEnable constructor.
     * @param \Ced\CsDeal\Model\DealFactory $dealFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Ced\CsDeal\Model\DealFactory $dealFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    )
    {
        $this->dealFactory = $dealFactory;
        $this->productFactory = $productFactory;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * Promo quote edit action
     *
     * @return                                  void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('deal_id');
        $deal_ids = explode(',', $ids);
        try {
            foreach ($deal_ids as $deal_id) {
                $model = $this->dealFactory->create()
                    ->load($deal_id)
                    ->setStatus('enabled');
                $model->save();
                $product = $this->productFactory->create()->load($model->getProductId());
                $product->setSpecialPrice($model->getDealPrice());
                $product->setSpecialFromDate($model->getStartDate());
                $product->setSpecialFromDateIsFormated(true);
                $product->setSpecialToDate($model->getEndDate());
                $product->setSpecialToDateIsFormated(true);
                $product->save();
            }

        } catch (Exception $e) {
            $msg = $e->getMessage();
            $this->messageManager->addErrorMessage(__($msg));
            $this->_redirect('csdeal/deal/listi');
            return;
        }
        $this->messageManager->addSuccessMessage(__('Status changed successfully'));
        $this->_redirect('csdeal/deal/listi');
        return;
    }

}
