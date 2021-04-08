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

namespace Ced\CsCmsPage\Controller\Adminhtml\Vblock;

/**
 * Class MassStatus
 * @package Ced\CsCmsPage\Controller\Adminhtml\Vblock
 */
class MassStatus extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Ced\CsCmsPage\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Ced\CsCmsPage\Model\CmspageFactory
     */
    protected $cmspageFactory;

    /**
     * MassStatus constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Ced\CsCmsPage\Model\BlockFactory $blockFactory
     * @param \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Ced\CsCmsPage\Model\BlockFactory $blockFactory,
        \Ced\CsCmsPage\Model\CmspageFactory $cmspageFactory
    )
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->blockFactory = $blockFactory;
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
        $block_id = $this->getRequest()->getParam('block_id');
        $status = $this->getRequest()->getParam('status', '');

        if (!is_array($block_id)) {
            $block_ids[] = $block_id;
        } else {
            $block_ids = $block_id;
        }
        foreach ($block_ids as $block_id) {


            if ($status == "approved") {
                $Vendorcmsblock = $this->blockFactory->create()->load($block_id, 'block_id');
                $Vendorcmsblock->setData('is_approve', 1);
                $Vendorcmsblock->save();
            } else {
                $Vendorcmsblock = $this->blockFactory->create()->load($block_id, 'block_id');
                $Vendorcmsblock->setData('is_approve', 0);
                $Vendorcmsblock->save();

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
        $model = $this->cmspageFactory_ > create();
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
