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

namespace Ced\Rewardsystem\Controller\Rewardpoint;

/**
 * Class Condition
 * @package Ced\Rewardsystem\Controller\Rewardpoint
 */
class Condition extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Ced\Rewardsystem\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $ruleCollectionFactory;

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
     * Condition constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ced\Rewardsystem\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\Rewardsystem\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    )
    {
        $this->_storeManager = $storeManager;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->cartFactory = $cartFactory;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context);
    }

    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->ruleCollectionFactory->create();
        $data->setOrder('sort_order', 'ASC');
        $condition = $data->getData();

        $count = 0;
        foreach ($condition as $key => $value) {

            $maincondition = $value['simple_condition'];

            $mainarray = json_decode($maincondition, true);
            $arraytraverse = $this->traverseArray($mainarray);

            $count = $count + 1;
        }
    }

    /**
     * @param $array
     * @param bool $flag
     * @param string $allOrAny
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    function traverseArray($array, $flag = false, $allOrAny = '')
    {

        $items = $this->cartFactory->create()->getItems();
        $count = 0;
        foreach ($items as $key => $item) {

            $catagoryname = '';
            $product[] = array(
                "product_id" => $item->getProduct()->getId(),
                "quote_item_qty" => $item->getQty(),
                "qty" => $item->getQty(),
                "base_row_total" => $item->getPrice(),
                "quote_item_price" => $item->getPrice(),
                'quote_item_row_total' => ($item->getPrice() * $item->getQty()),
            );


            $productCollection = $this->productFactory->create()->load($product[$count]['product_id']);
            $product[$count]['attribute_set_id'] = $productCollection->getAttributeSetId();
            $catagory = $productCollection->getCategoryIds();

            foreach ($catagory as $category_id) {
                $_cat = $this->categoryFactory->create()->setStoreId($this->_storeManager->getStore()->getId())->load($category_id);
                $catagoryname[] = $_cat->getName();


            }
            $catagoryNameAdd = implode(',', $catagoryname);
            $product[$count]['category_ids'] = $catagoryNameAdd;

            $count++;
        }

        $pointflag = 1;

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
                            $pointflag = $this->traverseArray($value['conditions'], $all_value, 'all');

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
                            $pointflag = $this->traverseArray($value['conditions'], $all_value, 'any');

                        }

                        if ($allOrAny == 'all' && $pointflag == '0')
                            return $pointflag;
                        if ($allOrAny == 'any' && $pointflag == '1')
                            return $pointflag;
                    }

                } elseif (array_key_exists('conditions', $value)) {

                    if ($value['aggregator'] == 'all') {
                        $all_value = $value['value'];
                        $pointflag = $this->traverseArray($value['conditions'], $all_value, 'all');
                        if ($allOrAny == 'any' && $pointflag == '0')
                            return $pointflag;
                    } elseif ($value['aggregator'] == 'any') {
                        $all_value = (int)$value['value'];
                        $pointflag = $this->traverseArray($value['conditions'], $all_value, 'any');
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

                        if ($value['attribute'] == 'quote_item_price' || $value['attribute'] == 'quote_item_qty' || $value['attribute'] == 'quote_item_row_total' || $value['attribute'] == 'attribute_set_id' || $value['attribute'] == 'category_ids') {
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
                                    $pointflag = !(${$value['attribute']} == $value['value'] xor $flag) ? 1 : 0;
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
    }

}