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
 * @package     Ced_CsCmsPage
 * @author   CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsCmsPage\Controller\Adminhtml\Vcmspage;

/**
 * Class MassStatus
 * @package Ced\CsCmsPage\Controller\Adminhtml\Vcmspage
 */
class MassStatus extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * @var \Ced\CsCmsPage\Model\CmspageFactory
     */
    protected $cmspageFactory;

    /**
     * MassStatus constructor.
     * @param \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        parent::__construct($context);

        $this->cmspageFactory = $cmspageFactory;
    }


    /**
     * Promo quote edit action
     *
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $inline = $this->getRequest()->getParam('inline', 0);
        $page_id = $this->getRequest()->getParam('page_id');
        $status = $this->getRequest()->getParam('status', '');

        if (!is_array($page_id)) {
            $page_ids[] = $page_id;
        } else {
            $page_ids = $page_id;
        }

        foreach ($page_ids as $page_id) {


            if ($status == "approved") {
                $Vendorcmspage = $this->cmspageFactory->create()->load($page_id, 'page_id');
                $Vendorcmspage->setData('is_approve', 1);
                $Vendorcmspage->save();
            } else {
                $Vendorcmspage = $this->cmspageFactory->create()->load($page_id, 'page_id');
                $Vendorcmspage->setData('is_approve', 0);
                $Vendorcmspage->save();

            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Validate batch of vendors before theirs status will be set
     *
     * @param array $vendorIds
     * @param String $status
     * @return Ced\CsMarketplace\Model\Vendor
     * @throws Exception
     */
    public function _validateMassStatus(array $vendorIds, $status)
    {
        $model = $this->cmspageFactory->create();
        if ($status == \Ced\CsMarketplace\Model\Vendor::VENDOR_APPROVED_STATUS) {
            if (!$model->validateMassAttribute('shop_url', $vendorIds)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Some of the processed vendors have no Shop URL value defined. Please fill it prior to performing operations on these vendors.')
                );
            }
        }
        return $model;
    }

}
