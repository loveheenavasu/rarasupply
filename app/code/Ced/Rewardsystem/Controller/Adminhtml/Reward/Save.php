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
 * @package     Ced_Rewardsystem
 * @author     CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Rewardsystem\Controller\Adminhtml\Reward;

use Magento\Backend\App\Action\Context;

/**
 * Class Save
 * @package Ced\Rewardsystem\Controller\Adminhtml\Reward
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $point['entity_id'] = isset($data['point_fieldset']['entity_id']) ? $data['point_fieldset']['entity_id'] : null;
        $point['point'] = isset($data['point_fieldset']['point']) ? $data['point_fieldset']['point'] : 0;
        $storeIds = isset($data['point_fieldset']['store_id']) ? $data['point_fieldset']['store_id'] : [];

        $model = $this->productFactory->create();

        if (!empty($storeIds)) {
            foreach ($storeIds as $id) {
                if ($id) {
                    $model->setStoreId($id)->load($point['entity_id']);
                    $model->setCedRpoint($point['point']);
                } else {
                    $model->load($point['entity_id']);
                    $model->setCedRpoint($point['point']);
                }
            }
        }
        $model->save();

        return $this->resultRedirectFactory->create()->setPath('*/*/catalogrule');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }
}