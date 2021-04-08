<?php
 /**
* CedCommerce
*
* NOTICE OF LICENSE
*
* This source file is subject to the End User License Agreement (EULA)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://cedcommerce.com/license-agreement.txt
*
* @category    Ced
* @package     Ced_Rewardsystem
* @author   	 CedCommerce Core Team <connect@cedcommerce.com >
* @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
* @license      http://cedcommerce.com/license-agreement.txt
*/  
namespace Ced\Rewardsystem\Model;
use \Magento\Rule\Model\AbstractModel;

 
class Rule extends AbstractModel
{
	protected $_conditions;
    

    /**
     * Rule type actions
     */
    const TO_PERCENT_ACTION = 'to_percent';

    const BY_PERCENT_ACTION = 'by_percent';

    const TO_FIXED_ACTION = 'to_fixed';

    const BY_FIXED_ACTION = 'by_fixed';

    const CART_FIXED_ACTION = 'cart_fixed';

    const BUY_X_GET_Y_ACTION = 'buy_x_get_y';

    /**
     * Store coupon code generator instance
     *
     * @var \Magento\SalesRule\Model\Coupon\CodegeneratorInterface
     */
    protected $_couponCodeGenerator;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'salesrule_rule';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';

    /**
     * Rule's primary coupon
     *
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $_primaryCoupon;

    

    /**
     * Store already validated addresses and validation results
     *
     * @var array
     */
    protected $_validatedAddresses = [];

    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $_couponFactory;

    /**
     * @var \Magento\SalesRule\Model\Coupon\CodegeneratorFactory
     */
    protected $_codegenFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\CombineFactory
     */
    protected $_condCombineFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    protected $_condProdCombineF;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\Collection
     */
    protected $_couponCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param CouponFactory $couponFactory
     * @param Coupon\CodegeneratorFactory $codegenFactory
     * @param Rule\Condition\CombineFactory $condCombineFactory
     * @param Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param ResourceModel\Coupon\Collection $couponCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegenFactory,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_couponFactory = $couponFactory;
        $this->_codegenFactory = $codegenFactory;
        $this->_condCombineFactory = $condCombineFactory;
        $this->_condProdCombineF = $condProdCombineF;
        $this->_couponCollection = $couponCollection;
        $this->_storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct() {
        $this->_init('Ced\Rewardsystem\Model\ResourceModel\Rule');
    }    

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {

        return $this->_condCombineFactory->create();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->_condProdCombineF->create();
    }

    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * @param string $formName
     * @return string
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }
 /*    public function afterSave()
    {
        $couponCode = trim($this->getCouponCode());
        if (strlen(
            $couponCode
        ) && $this->getCouponType() == self::COUPON_TYPE_SPECIFIC && !$this->getUseAutoGeneration()
        ) {
            $this->getPrimaryCoupon()->setCode(
                $couponCode
            )->setUsageLimit(
                $this->getUsesPerCoupon() ? $this->getUsesPerCoupon() : null
            )->setUsagePerCustomer(
                $this->getUsesPerCustomer() ? $this->getUsesPerCustomer() : null
            )->setExpirationDate(
                $this->getToDate()
            )->save();
        } else {
            $this->getPrimaryCoupon()->delete();
        }

        parent::afterSave();
        return $this;
    }*/

    /**
     * Initialize rule model data from array.
     * Set store labels if applicable.
     *
     * @param array $data
     * @return $this
     */
    public function loadPost(array $data)
    {
        parent::loadPost($data);

        if (isset($data['store_labels'])) {
            $this->setStoreLabels($data['store_labels']);
        }

        return $this;
    }
}
    