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

use Magento\Framework\Stdlib\DateTime\Timezone;

/**
 * Class Saverule
 * @package Ced\Rewardsystem\Controller\Adminhtml\Reward
 */
class Saverule extends \Magento\SalesRule\Controller\Adminhtml\Promo\Quote
{
    /**
     * @var \Ced\Rewardsystem\Model\RuleFactory
     */
    protected $ruleFactory;
    /**
     * @var Timezone
     */
    protected $_timeZone;

    /**
     * Saverule constructor.
     * @param \Ced\Rewardsystem\Model\RuleFactory $ruleFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     */
    public function __construct(
        \Ced\Rewardsystem\Model\RuleFactory $ruleFactory,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        Timezone $timezone,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
    )
    {
        $this->ruleFactory = $ruleFactory;
        $this->_timeZone = $timezone;
        parent::__construct($context, $coreRegistry, $fileFactory, $dateFilter);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();

        unset($data['simple_condition']);

        if(isset($data['from_date']) && !$data['from_date']){
            $currDate = date('m/d/Y', $this->_timeZone->scopeTimeStamp());
            $data['from_date'] = $currDate;
        }
        $fromDate = strtotime($data['from_date']);
        $toDate = strtotime($data['to_date']);

        if (!!$fromDate && !!$toDate && $fromDate > $toDate) {
            $this->messageManager->addErrorMessage(__('From Date Must Be less Than Or Equal to ToDate.'));
            $id = $this->getRequest()->getParam('rule_id', false);
            $param = [];
            if($id){
                $param = ['id' => $id, 'rule_id' => $id];
            }
            return $this->_redirect('*/*/addrule', $param);

        } else {
            if ($this->getRequest()->getPostValue()) {
                $ruleid = $this->getRequest()->getParam('rule_id');
                if ($ruleid) {
                    $model = $this->ruleFactory->create()->load($ruleid);
                } else {
                    $model = $this->ruleFactory->create();
                }
                try {

                    if (isset($data['rule']['conditions'])) {
                        $data['conditions'] = $data['rule']['conditions'];
                    }
                    if (isset($data['rule']['actions'])) {
                        $data['actions'] = $data['rule']['actions'];
                    }
                    $arr = $this->_convertFlatToRecursive($data);

                    unset($data['rule']);


                    if (isset($arr['conditions']))
                        $condition = json_encode($arr['conditions'], true);

                    if (isset($arr['conditions']))
                        $model->setSimpleCondition($condition);
                    $model->setName($data['name']);
                    $model->setDescription($data['description']);
                    $model->setSortOrder($data['sort_order']);
                    $model->setIsActive($data['is_active']);
                    $model->setStopRulesProcessing($data['stop_rules_processing']);
                    $model->setFromDate($data['from_date']);
                    $model->setToDate($data['to_date']);
                    $model->setPointX($data['point_x']);
                    $model->setSubIsEnable($data['stop_rules_processing']);
                    unset($data['rule_id']);
                    $model->loadPost($data);
                    $model->save();
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                }
            }
        }
        $this->messageManager->addSuccessMessage(__('You saved the rule.'));
        $this->_redirect('*/*/shoppingcartrule');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * @param array $data
     * @return array
     */
    public function _convertFlatToRecursive(array $data)
    {
        $arr = [];

        foreach ($data as $key => $value) {
            if (($key === 'conditions' || $key === 'actions') && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = &$arr;
                    for ($i = 0, $l = sizeof($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = &$node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            }
        }
        return $arr;
    }

}
