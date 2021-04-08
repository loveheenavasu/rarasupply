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

namespace Ced\Rewardsystem\Block\Checkout\MiniCart\Summary;

use Magento\Framework\Stdlib\DateTime\Timezone;

/**
 * Class Summary
 * @package Ced\Rewardsystem\Block\Checkout\MiniCart\Summary
 */
class Summary extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var Timezone
     */
    protected $_timeZone;

    /**
     * @var \Ced\Rewardsystem\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $cartFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Ced\Rewardsystem\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * Summary constructor.
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Ced\Rewardsystem\Model\RuleFactory $ruleFactory
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Timezone $timezone
     * @param \Ced\Rewardsystem\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Ced\Rewardsystem\Model\RuleFactory $ruleFactory,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        Timezone $timezone,
        \Ced\Rewardsystem\Helper\Data $helperData,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_timeZone = $timezone;
        $this->_helperData = $helperData;
        $this->cartFactory = $cartFactory;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function SummaryData()
    {

        $this->_checkoutSession->unsTotalProductpoint();
        $results = [];
        $result = 0;
        $items = $this->cartFactory->create()->getItems();
        $productshow = $this->_helperData;
        $sameProductPoint =
            $productshow->getStoreConfig('reward/setting/product_point', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $maxOrderPoint = $productshow->getStoreConfig('reward/setting/max_point', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $maxOrderPointEnable = $productshow->getStoreConfig('reward/setting/max_point_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $count = 0;

        foreach ($items as $key => $item) {
            if ($item->getParentItemId())
                continue;
            $catagoryname = [];
            $product[] = array(
                "product_id" => $item->getProduct()->getId(),
                "quote_item_qty" => $item->getQty(),
                "qty" => $item->getQty(),
                "base_row_total" => $item->getPrice(),
                "quote_item_price" => $item->getPrice(),
                'quote_item_row_total' => ($item->getPrice() * $item->getQty()),
            );
            $productCollection = $this->productFactory->create()->load($product[$count]['product_id']);
            $point = $productCollection->getCedRpoint();

            $updatepoint = 0;
            $updatepoint = $this->_checkoutSession->getTotalProductpoint();

            if ($point !== '0') {
                if ($point) {
                    $minicartpoint = $updatepoint + ($point * $item->getQty());
                    $this->_checkoutSession->setTotalProductpoint($minicartpoint);

                } else {
                    if ($sameProductPoint) {

                        $minicartpoint = $updatepoint + ($sameProductPoint * $item->getQty());
                        $this->_checkoutSession->setTotalProductpoint($minicartpoint);
                    } else {
                        $this->_checkoutSession->getTotalProductpoint();
                    }
                }
            } else {
                $this->_checkoutSession->setTotalProductpoint(0);
            }


            $catagory = $productCollection->getCategoryIds();

            foreach ($catagory as $category_id) {
                $_cat = $this->categoryFactory->create()->setStoreId($this->_storeManager->getStore()->getId())->load($category_id);
                $catagoryname[] = $_cat->getName();
            }

            $catagoryNameAdd = implode(',', $catagoryname);
            $product[$count]['category_ids'] = $catagoryNameAdd;
            $product[$count]['attribute_set_id'] = $productCollection->getAttributeSetId();
            $count++;
        }

        $point = $this->_checkoutSession->getTotalProductpoint();

        $intCurrentTime = $this->_timeZone->scopeTimeStamp();
        $currDate = date('Y-m-d', $intCurrentTime);

        $data = $this->ruleFactory->create()->getCollection();

        $data->setOrder('sort_order', 'ASC')->addFieldToFilter('is_active', '1')
            ->addFieldToFilter('from_date', ['lteq' => $currDate]);
        $data->getSelect()->where("to_date IS NULL OR to_date >= $currDate");

        $condition = $data->getData();

        $result = 0;
        $flag = false;

        if (!empty($condition) && $point !== 0) {
            foreach ($condition as $key => $value) {
                $value['point_x'] = floatval($value['point_x']);
                $maincondition = $value['simple_condition'];
                $mainarray = json_decode($maincondition, true);

                if ($value['stop_rules_processing'] == 1) {

                    $arraytraverse = $this->traverseArray($mainarray, $product);

                    if ($arraytraverse == 1) {
                        if ($value['check_point_in'] == 1) {
                            $quote = $this->cartFactory->create()->getQuote();
                            $base_subtotal = $quote->getSubtotal();
                            $perPoint = floor($base_subtotal * ($value['point_x'] / 100));
                            $result += $perPoint;
                            $results['summaryPoint'] = $result;

                        } else {
                            $result += $value['point_x'];
                            $results['summaryPoint'] = $result;
                        }
                    }
                    break;
                }

                $arraytraverse = $this->traverseArray($mainarray, $product);

                if ($arraytraverse == 1) {
                    if ($value['check_point_in'] == 1) {
                        $quote = $this->cartFactory->create()->getQuote();
                        $base_subtotal = $quote->getSubtotal();
                        $perPoint = floor($base_subtotal * ($value['point_x'] / 100));

                        $result = $result;
                        $results['summaryPoint'] = $result + $perPoint;

                    } else {
                        try {

                            $result = $result + $value['point_x'];
                            $results['summaryPoint'] = $result;

                        } catch (\Exception $e) {
                            throw new \Magento\Framework\Exception\LocalizedException (__($e->getMessage()));
                        }
                    }
                }
            }
            if (isset($results['summaryPoint']))
                $results['summaryPoint'] = $results['summaryPoint'] + $point;
            else
                $results['summaryPoint'] = $point;
        } else {
            $results['summaryPoint'] = $point;
        }

        if ($maxOrderPoint < $this->_checkoutSession->getTotalProductpoint() && $maxOrderPointEnable == 1) {
            $results['summaryPoint'] = $maxOrderPoint;
            $this->_checkoutSession->setTotalProductpoint($maxOrderPoint);
        }

        $this->_checkoutSession->setFinalConditionpoint($results['summaryPoint']);
        $results['customerLogin'] = $this->_customerSession->isLoggedIn();

        return $results;

    }

    /**
     * @param $array
     * @param $product
     * @param bool $flag
     * @param string $allOrAny
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function traverseArray($array, $product, $flag = false, $allOrAny = '')
    {
        $pointflag = 1;
        if($array) {
            try {
                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        if (array_key_exists('operator', $value) && array_key_exists('aggregator', $value)) {
                            if ($value['aggregator'] == 'all') {
                                $all_value = $value['value'];
                                switch ($value['operator']) {
                                    case '==':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !($productvalue[$value['attribute']] == $all_value xor $flag) ? 1 : 0;
                                            if ($pointflag == '0') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '>=':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !($productvalue[$value['attribute']] >= $all_value xor $flag) ? 1 : 0;
                                            if ($pointflag == '0') {
                                                return $pointflag;
                                            }

                                        }
                                        break;
                                    case '<=':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !($productvalue[$value['attribute']] <= $all_value xor $flag) ? 1 : 0;
                                            if ($pointflag == '0') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '<':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !($productvalue[$value['attribute']] < $all_value xor $flag) ? 1 : 0;
                                            if ($pointflag == '0') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '>':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !($productvalue[$value['attribute']] == $all_value xor $flag) ? 1 : 0;
                                            if ($pointflag == '0') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '!=':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !($productvalue[$value['attribute']] != $all_value xor $flag) ? 1 : 0;
                                            if ($pointflag == '0') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '{}':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !(strpos($productvalue[$value['attribute']], $all_value) xor $flag) ? 1 : 0;
                                            if ($pointflag == '0') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '!{}':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !(strpos($productvalue[$value['attribute']], $all_value) xor $flag) ? 0 : 1;
                                            if ($pointflag == '0') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '()':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !(strpos($productvalue[$value['attribute']], $all_value) xor $flag) ? 1 : 0;
                                            if ($pointflag == '0') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '!()':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !(strpos($productvalue[$value['attribute']], $all_value) xor $flag) ? 0 : 1;
                                            if ($pointflag == '0') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                }
                                if ($pointflag == '0') {
                                    return $pointflag;
                                } elseif ($pointflag == '1' && array_key_exists('conditions', $value)) {
                                    $all_value = 1;
                                    $pointflag = $this->traverseArray($value['conditions'], $product, $all_value, 'all');

                                }

                                if ($allOrAny == 'any' && $pointflag == '0')
                                    return $pointflag;
                            } elseif ($value['aggregator'] == 'any') {
                                $all_value = (int)$value['value'];
                                switch ($value['operator']) {
                                    case '==':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !($productvalue[(string)$value['attribute']] == $all_value xor $flag) ? 1 : 0;
                                            if ($pointflag == '1') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '>=':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !($productvalue[$value['attribute']] >= $all_value xor $flag) ? 1 : 0;
                                            if ($pointflag == '1') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '<=':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !($productvalue[$value['attribute']] <= $all_value xor $flag) ? 1 : 0;
                                            if ($pointflag == '1') {
                                                return $pointflag;
                                            }

                                        }
                                        break;
                                    case '<':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !($productvalue[$value['attribute']] < $all_value xor $flag) ? 1 : 0;
                                            if ($pointflag == '1') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '>':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !($productvalue[$value['attribute']] > $all_value xor $flag) ? 1 : 0;
                                            if ($pointflag == '1') {
                                                return $pointflag;
                                            }
                                        }

                                        break;
                                    case '!=':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !($productvalue[$value['attribute']] != $all_value xor $flag) ? 1 : 0;
                                            if ($pointflag == '1') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '{}':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !(strpos($productvalue[$value['attribute']], $all_value) xor $flag) ? 1 : 0;
                                            if ($pointflag == '1') {
                                                return $pointflag;
                                            }
                                        }
                                        break;

                                    case '!{}':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !(strpos($productvalue[$value['attribute']], $all_value) xor $flag) ? 0 : 1;
                                            if ($pointflag == '1') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '()':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !(strpos($productvalue[$value['attribute']], $all_value) xor $flag) ? 1 : 0;
                                            if ($pointflag == '1') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                    case '!()':
                                        foreach ($product as $key => $productvalue) {
                                            $pointflag = !(strpos($productvalue[$value['attribute']], $all_value) xor $flag) ? 0 : 1;
                                            if ($pointflag == '1') {
                                                return $pointflag;
                                            }
                                        }
                                        break;
                                }
                                if ($pointflag == '1') {
                                    return $pointflag;
                                } elseif ($pointflag == '0' && array_key_exists('conditions', $value)) {
                                    $all_value = 1;
                                    $pointflag = $this->traverseArray($value['conditions'], $product, $all_value, 'any');

                                }

                                if ($allOrAny == 'all' && $pointflag == '0')
                                    return $pointflag;
                                if ($allOrAny == 'any' && $pointflag == '1')
                                    return $pointflag;
                            }

                        } elseif (array_key_exists('conditions', $value)) {
                            if ($value['aggregator'] == 'all') {
                                $all_value = $value['value'];
                                $pointflag = $this->traverseArray($value['conditions'], $product, $all_value, 'all');
                                if ($allOrAny == 'any' && $pointflag == '0')
                                    return $pointflag;
                            } elseif ($value['aggregator'] == 'any') {
                                $all_value = (int)$value['value'];
                                $pointflag = $this->traverseArray($value['conditions'], $product, $all_value, 'any');
                                if ($allOrAny == 'all' && $pointflag == '0')
                                    return $pointflag;
                                if ($allOrAny == 'any' && $pointflag == '1')
                                    return $pointflag;
                            }
                        } else {
                            if ($allOrAny == 'any') {
                                $pointflag = 0;
                                if ($value['attribute'] == 'quote_item_price' || $value['attribute'] == 'quote_item_qty' || $value['attribute'] == 'quote_item_row_total' || $value['attribute'] == 'attribute_set_id') {
                                    switch ($value['operator']) {
                                        case '==':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !($productvalue[(string)$value['attribute']] == (int)$value['value'] xor $flag) ? 1 : 0;
                                                if ($pointflag == '1') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '>=':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !($productvalue[$value['attribute']] >= $value['value'] xor $flag) ? 1 : 0;
                                                if ($pointflag == '1') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '<=':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !($productvalue[$value['attribute']] <= $value['value'] xor $flag) ? 1 : 0;
                                                if ($pointflag == '1') {
                                                    return $pointflag;
                                                }

                                            }
                                            break;
                                        case '<':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !($productvalue[$value['attribute']] < $value['value'] xor $flag) ? 1 : 0;
                                                if ($pointflag == '1') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '>':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !($productvalue[$value['attribute']] > $value['value'] xor $flag) ? 1 : 0;
                                                if ($pointflag == '1') {
                                                    return $pointflag;
                                                }
                                            }

                                            break;
                                        case '!=':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !($productvalue[$value['attribute']] != $value['value'] xor $flag) ? 1 : 0;
                                                if ($pointflag == '1') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '{}':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !(strpos($productvalue[$value['attribute']], $value['value']) xor $flag) ? 1 : 0;
                                                if ($pointflag == '1') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;

                                        case '!{}':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !(strpos($productvalue[$value['attribute']], $value['value']) xor $flag) ? 0 : 1;
                                                if ($pointflag == '1') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '()':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !(strpos($productvalue[$value['attribute']], $value['value']) xor $flag) ? 1 : 0;
                                                if ($pointflag == '1') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '!()':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !(strpos($productvalue[$value['attribute']], $value['value']) xor $flag) ? 0 : 1;
                                                if ($pointflag == '1') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                    }
                                    if ($pointflag == '1') {
                                        return $pointflag;
                                    }
                                } elseif ($value['attribute'] == 'category_ids') {
                                    switch ($value['operator']) {
                                        case '==':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !(strpos($productvalue[$value['attribute']], $value['value']) xor $flag) ? 1 : 0;
                                                if ($pointflag == '1') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '!=':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !(strpos($productvalue[$value['attribute']], $value['value']) xor $flag) ? 0 : 1;
                                                if ($pointflag == '1') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;

                                    }
                                    if ($pointflag == '1') {
                                        return $pointflag;
                                    }
                                } else {
                                    switch ($value['operator']) {
                                        case '==':
                                            $pointflag = !(${$value['attribute']} == (int)$value['value'] xor $flag) ? 1 : 0;
                                            break;
                                        case '>=':
                                            $pointflag = !(${$value['attribute']} >= $value['value'] xor $flag) ? 1 : 0;
                                            break;
                                        case '<=':
                                            $pointflag = !(${$value['attribute']} <= $value['value'] xor $flag) ? 1 : 0;
                                            break;
                                        case '<':
                                            $pointflag = !(${$value['attribute']} < $value['value'] xor $flag) ? 1 : 0;
                                            break;
                                        case '>':
                                            $pointflag = !(${$value['attribute']} > $value['value'] xor $flag) ? 1 : 0;
                                            break;
                                    }
                                    if ($pointflag == '1') {
                                        return $pointflag;
                                    }
                                }
                            } elseif ($allOrAny == "all") {
                                if ($value['attribute'] == 'quote_item_price' || $value['attribute'] == 'quote_item_qty' || $value['attribute'] == 'quote_item_row_total' || $value['attribute'] == 'attribute_set_id') {
                                    switch ($value['operator']) {
                                        case '==':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !($productvalue[$value['attribute']] == (int)$value['value'] xor $flag) ? 1 : 0;
                                                if ($pointflag == '0') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '>=':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !($productvalue[$value['attribute']] >= (int)$value['value'] xor $flag) ? 1 : 0;
                                                if ($pointflag == '0') {
                                                    return $pointflag;
                                                }

                                            }
                                            break;
                                        case '<=':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !($productvalue[$value['attribute']] <= (int)$value['value'] xor $flag) ? 1 : 0;
                                                if ($pointflag == '0') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '<':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !($productvalue[$value['attribute']] < $value['value'] xor $flag) ? 1 : 0;
                                                if ($pointflag == '0') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '>':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !($productvalue[$value['attribute']] == $value['value'] xor $flag) ? 1 : 0;
                                                if ($pointflag == '0') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '!=':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !($productvalue[$value['attribute']] != $value['value'] xor $flag) ? 1 : 0;
                                                if ($pointflag == '0') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '{}':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !(strpos($productvalue[$value['attribute']], $value['value']) xor $flag) ? 1 : 0;
                                                if ($pointflag == '0') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '!{}':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !(strpos($productvalue[$value['attribute']], $value['value']) xor $flag) ? 0 : 1;
                                                if ($pointflag == '0') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '()':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !(strpos($productvalue[$value['attribute']], $value['value']) xor $flag) ? 1 : 0;
                                                if ($pointflag == '0') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '!()':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !(strpos($productvalue[$value['attribute']], $value['value']) xor $flag) ? 0 : 1;
                                                if ($pointflag == '0') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                    }
                                    if ($pointflag == '0') {
                                        return $pointflag;
                                    }
                                } elseif ($value['attribute'] == 'category_ids') {
                                    switch ($value['operator']) {
                                        case '==':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !(strpos($productvalue[$value['attribute']], $value['value']) xor $flag) ? 1 : 0;
                                                if ($pointflag == '0') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                        case '!=':
                                            foreach ($product as $key => $productvalue) {
                                                $pointflag = !(strpos($productvalue[$value['attribute']], $value['value']) xor $flag) ? 0 : 1;
                                                if ($pointflag == '0') {
                                                    return $pointflag;
                                                }
                                            }
                                            break;
                                    }
                                    if ($pointflag == '0') {
                                        return $pointflag;
                                    }
                                } else {
                                    switch ($value['operator']) {
                                        case '==':
                                            $pointflag = !($value['attribute'] == (int)$value['value'] xor $flag) ? 1 : 0;

                                            break;
                                        case '>=':
                                            $pointflag = !($value['attribute'] >= $value['value'] xor $flag) ? 1 : 0;
                                            break;
                                        case '<=':
                                            $pointflag = !($value['attribute'] <= $value['value'] xor $flag) ? 1 : 0;
                                            break;
                                        case '<':
                                            $pointflag = !($value['attribute'] < $value['value'] xor $flag) ? 1 : 0;
                                            break;
                                        case '>':
                                            $pointflag = !($value['attribute'] > $value['value'] xor $flag) ? 1 : 0;
                                            break;
                                    }
                                    if ($pointflag == '0') {
                                        return $pointflag;
                                    }
                                }
                            }

                        }

                    } else {
                        return $pointflag;

                    }
                }
                return $pointflag;
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException (__($e->getMessage()));
            }
        }return $pointflag;
    }
}
