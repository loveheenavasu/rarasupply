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

namespace Ced\Rewardsystem\Controller\Adminhtml\Reward;

use Magento\Backend\App\Action\Context;

/**
 * Class Ruledelete
 * @package Ced\Rewardsystem\Controller\Adminhtml\Reward
 */
class Ruledelete extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\Rewardsystem\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * Ruledelete constructor.
     * @param \Ced\Rewardsystem\Model\RuleFactory $ruleFactory
     * @param Context $context
     */
    public function __construct(
        \Ced\Rewardsystem\Model\RuleFactory $ruleFactory,
        Context $context
    )
    {
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $postData = $this->getRequest()->getParam('id');
        if (!empty($postData)) {
            try {
                $ruleModel = $this->ruleFactory->create()->load($postData);
                $rule = $ruleModel->getName();
                $ruleModel->delete();

                $this->messageManager->addSuccessMessage(__('You Deleted ' . $rule . ' Successfully'));
                $this->_redirect('*/*/shoppingcartrule');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $this->_redirect('*/*/shoppingcartrule');
            }
        }
    }
}
