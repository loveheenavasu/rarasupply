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
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Rewardsystem\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 * @package Ced\Rewardsystem\Block\Adminhtml\Edit
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var \Ced\Rewardsystem\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * DeleteButton constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Ced\Rewardsystem\Model\RuleFactory $ruleFactory
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Ced\Rewardsystem\Model\RuleFactory $ruleFactory
    )
    {
        $this->ruleFactory = $ruleFactory;
        $this->request = $context->getRequest();
        parent::__construct($context, $registry);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $params = $this->request->getParams();
        $data = [];


        if (isset($params['id'])) {
            $rule = $this->ruleFactory->create()->load($params['id']);
            if ($rule->getRuleId()) {

                $deleteUrl = $this->getUrl('*/*/ruledelete', ['id' => $params['id']]);
                $data = [
                    'label' => __('Delete Rule'),
                    'class' => 'action-secondary',
                    'id' => 'fee-edit-delete-button',
                    'data_attribute' => [
                        'url' => $deleteUrl
                    ],
                    'on_click' => 'deleteConfirm(\'' . __(
                            'Are you sure you want to do this?'
                        ) . '\', \'' . $deleteUrl . '\')',
                    'sort_order' => 20,
                ];
            }
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/ruledelete', ['id' => $this->getRuleId()]);
    }
}
