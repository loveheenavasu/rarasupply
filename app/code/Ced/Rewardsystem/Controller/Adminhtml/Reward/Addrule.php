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

use Magento\Framework\View\Result\PageFactory;

/**
 * Class Addrule
 * @package Ced\Rewardsystem\Controller\Adminhtml\Reward
 */
class Addrule extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Ced\Rewardsystem\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * Addrule constructor.
     * @param \Ced\Rewardsystem\Model\RuleFactory $ruleFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Ced\Rewardsystem\Model\RuleFactory $ruleFactory,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter);
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParams();
        $model = $this->ruleFactory->create();
        $this->_coreRegistry->register(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE, $model);
        $this->_coreRegistry->register('current_promo_catalog_rule', $model);

        if (!empty($id['id'])) {
            $model->load($id['id']);
            $this->_coreRegistry->register('rewardrule_form_data', $id['id']);
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Rewardsystem::reward_manage');
        $resultPage->getConfig()->getTitle()->prepend(__('Reward System'));
        $resultPage->addBreadcrumb(__('Ced'), __('Ced'));
        $resultPage->addBreadcrumb(__('Hello World'), __('Reward System'));

        return $resultPage;
    }

    /**
     * Check permission via ACL resource
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_Rewardsystem::shoppingcartrule');
    }
}
