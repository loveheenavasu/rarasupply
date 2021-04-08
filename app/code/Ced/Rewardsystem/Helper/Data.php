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

namespace Ced\Rewardsystem\Helper;

use \Ced\Rewardsystem\Model\Regisuserpoint;

/**
 * Class Data
 * @package Ced\Rewardsystem\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var Regisuserpoint
     */
    protected $regisuserpoint;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Ced\Rewardsystem\Model\EmailSender
     */
    protected $emailSender;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $state;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Regisuserpoint $regisuserpoint
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $state
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Regisuserpoint $regisuserpoint,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $state,
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->_inlineTranslation = $inlineTranslation;
        $this->date = $date;
        $this->regisuserpoint = $regisuserpoint;
        $this->customerFactory = $customerFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->state = $state;
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function isEnable()
    {
        $configvalue = $this->scopeConfig;
        $value = $configvalue->getValue('advanced/modules_disable_output/Ced_Rewardsystem');

        return $value;
    }

    /**
     * @param string $customerId
     * @return array
     */
    public function getCustomerWisePointSheet($customerId = '')
    {
        $return = [];
        $date_wise_collection = $this->getPointDataDateWise($customerId, false, true);
        $data = isset($date_wise_collection['customer_point_sheet']) ? $date_wise_collection['customer_point_sheet'] : [];

        foreach ($data as $customer_id => $customer_point_details) {
            if ($this->customerFactory->create()->load($customer_id)->getId())
                $return[$customer_id] = $this->setCustomerWisePointSheet($customer_point_details);
        }

        return $return;
    }

    /**
     * The idea is to loop through each points, check if used points are there,
     *  deduct these used point from earned points which can be expired in future.
     * for deduction of used points, if the point is to be deducted from expired earned points
     * then that expired earned point should be received before order with used point.
     * @param array $point_collection
     * @return mixed
     */
    public function setCustomerWisePointSheet($point_collection = [])
    {
        $return['points'] = $non_expired_points = 0;
        $expiration_point_collection = [];
        $return['points_data'] = isset($point_collection['points_data']) ? $point_collection['points_data'] : [];
        $return['expiration_wise_data'] = isset($point_collection['expiration_wise_data']) ? $point_collection['expiration_wise_data'] : [];

        foreach ($return['points_data'] as $pkey => $point_data) {
            $used_points = $expired_points = $redeem_points = 0;

            //retrieving points create time to compare the expire date of previous earned point with the current point
            //in order to deduct used points from the previous earned expired point which is yet not expired.
            $create_datetime = strtotime($point_data['creating_date']);
            $id = $point_data['id'];

            //only non cancelled used points
            if ($point_data['status'] != \Magento\Sales\Model\Order::STATE_CANCELED)
                $used_points += $point_data['point_used'];
            else
                $redeem_points += $point_data['point_used'];

            //retrieving all expiration date wise points so to deduct used points from expiration point first
            /*
             * Now here the calculation checks if the current point received from order has been received between the
             * expired-point's order time and its's expired time
             * including the current point if its an expiration point
             */
            $new_expiration_point_collection = array_filter(
                $return['expiration_wise_data'],
                function ($values) use ($create_datetime, $id) {
                    return (
                        isset($values['expiration_date']) && $values['status'] == \Magento\Sales\Model\Order::STATE_COMPLETE
                        && (
                            (
                                (strtotime($values['expiration_date']) >= $create_datetime) &&
                                (isset($values['updated_at']) && (strtotime($values['updated_at']) <= $create_datetime))
                            )
                            ||
                            ($id == $values['id'])
                        )
                    );
                }
            );

            /*
             * check if the previous expiry record exist in new record collection.
             * If previous entry exist then don't override the previous entry.
             * As previous entry contains the deducted received points
            */
            if (!empty($expiration_point_collection) && !empty(array_intersect(array_column($expiration_point_collection, 'id'), array_column($new_expiration_point_collection, 'id'))))
                $expiration_point_collection += $new_expiration_point_collection;
            else
                $expiration_point_collection = $new_expiration_point_collection;

            //oldest expiration point deduct first
            $expire_date_keys = array_column($expiration_point_collection, 'expiration_date');
            array_multisort($expire_date_keys, SORT_ASC, $expiration_point_collection);


            /*
             * deduction of used points.
             * Now the idea is the current expired point should not be included in the deduction process
             * as the point was received after the point used(after order placed).
             * The receive point in expiration point collection gets change after deducting the used points from them
             * This record will be again used in the above filter loop.
            */
            foreach ($expiration_point_collection as $key => $data) {
                if (isset($data['received_point']) && (int)$data['received_point'] > 0 && !empty($used_points) && (strtotime($data['creating_date']) != $create_datetime)) {
                    if ($used_points <= $data['received_point']) {
                        $expiration_point_collection[$key]['received_point'] -= $used_points;
                        $used_points . ' = ';
                        $used_points = 0;
                    } else {
                        $used_points -= $data['received_point'];
                        $expiration_point_collection[$key]['received_point'] = 0;
                    }
                }
                $expired_points += $expiration_point_collection[$key]['received_point'];
            }

            //the point to use in calculation
            $non_expired_points -= $used_points;

            //now add the received non expire points
            if (empty($point_data['expiration_date']) && $point_data['status'] == \Magento\Sales\Model\Order::STATE_COMPLETE)
                $non_expired_points += isset($point_data['received_point']) ? $point_data['received_point'] : 0;

            $return['points_data'][$pkey]['redeem_points'] = $redeem_points;
            $return['points_data'][$pkey]['non_expire_points'] = $non_expired_points;
            $return['points_data'][$pkey]['expired_points'] = $expired_points;

            //the point to display to frontend
            $return['points'] = $return['points_data'][$pkey]['display_points'] = $non_expired_points + $expired_points;

        }

        //get current date
        $current_date = strtotime($this->date->gmtDate('Y-m-d H:i:s'));

        //check if current date is greater than expiration date of expiration point collection
        /*
         * Now the idea is, if the last entry was of an expired point and the expired date is less than current date
         * then display point should deduct those expired points.
        */
        if (!empty($expiration_point_collection)) {
            foreach ($expiration_point_collection as $key => $expired_point_data) {
                if (!empty($expired_point_data['received_point']) && !empty($expired_point_data['expiration_date']) && $current_date > strtotime(($expired_point_data['expiration_date']))) {
                    $return['points'] -= $expired_point_data['received_point'];
                    $expiration_point_collection[$key]['received_point'] = 0;
                }
            }
        }

        //modified collection of expiration points
        $return['expiration_wise_data_after_deduction'] = $expiration_point_collection;

        return $return;
    }

    /**
     * @param string $customerId
     * @param bool $creation_wise
     * @param bool $expiration_wise
     * @return array
     */
    public function getPointDataDateWise($customerId = '', $creation_wise = true, $expiration_wise = true)
    {
        $return = [];
        $collection = $this->regisuserpoint->getCollection();
        if (!empty($customerId)) $collection->addFieldToFilter('customer_id', $customerId);

        $all_points_data = $collection->getData();

        foreach ($all_points_data as $key => $point_data) {
            $return['customer_point_sheet'][$point_data['customer_id']]['points_data'][] = $point_data;

            if ($expiration_wise && !empty($point_data['expiration_date'])) {
                $point_data['expire_date'] = $this->date->gmtDate('Y-m-d', $point_data['expiration_date']);
                $return['customer_point_sheet'][$point_data['customer_id']]['expiration_wise_data'][] = $point_data;
            }

            if ($creation_wise && !empty($point_data['creating_date'])) {
                $create_date = $this->date->gmtDate('Y-m-d', $point_data['creating_date']);
                $return['customer_point_sheet'][$point_data['customer_id']]['creation_wise_data'][strtotime($create_date)][] = $point_data;
            }
        }

        return $return;
    }

    /**
     * @param $value
     * @param string $store
     * @return mixed
     */
    public function getStoreConfig($value, $store = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    /**
     * @param $emails
     * @param $message
     * @param $subject
     * @param $referral_url
     * @param $customer_Id
     * @return bool|int
     */
    public function sendInvitationEmail($emails, $message, $subject, $referral_url, $customer_Id)
    {
        try {
            $support = $this->scopeConfig->getValue('reward/setting/support_email');

            $modeldata = $this->customerFactory->create()->load($customer_Id);

            $emailvariables['customername'] = $modeldata->getName();
            $emailvariables['storename'] = $this->scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $emailvariables['subject'] = $subject;
            $emailvariables['message'] = $message;
            $emailvariables['referral_url'] = $referral_url;


            $this->_template = "ced_reward_referal_email";
            $this->_inlineTranslation->suspend();
            $senderInfo = [
                'name' => 'SUPPORT',
                'email' => $support,
            ];
            if (!empty($support)) {
                $sent = 0;
                try {
                    foreach ($emails as $email) {


                        $transport = $this->_transportBuilder
                            ->setTemplateIdentifier('ced_reward_referal_email')// this code we have mentioned in the email_templates.xml
                            ->setTemplateOptions(
                                [
                                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                                ]
                            )
                            ->setTemplateVars($emailvariables)
                            ->setFrom($senderInfo)
                            ->addTo($email)
                            ->getTransport();

                        $transport->sendMessage();
                        $this->state->resume();
                        $sent++;
                    }
                } catch (\Exception $e) {
                    return false;
                }
                return $sent;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $array
     * @param $product
     * @param $quoteId
     * @param bool $flag
     * @param string $allOrAny
     * @return int
     */
    public function apiTraverseArray($array, $product, $quoteId, $flag = false, $allOrAny = '')
    {

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
                            $pointflag = $this->apiTraverseArray($value['conditions'], $product, $quoteId, $all_value, 'all');

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
                            $pointflag = $this->apiTraverseArray($value['conditions'], $product, $quoteId, $all_value, 'any');

                        }

                        if ($allOrAny == 'all' && $pointflag == '0')
                            return $pointflag;
                        if ($allOrAny == 'any' && $pointflag == '1')
                            return $pointflag;
                    }

                } elseif (array_key_exists('conditions', $value)) {
                    if ($value['aggregator'] == 'all') {
                        $all_value = $value['value'];
                        $pointflag = $this->apiTraverseArray($value['conditions'], $product, $quoteId, $all_value, 'all');
                        if ($allOrAny == 'any' && $pointflag == '0')
                            return $pointflag;
                    } elseif ($value['aggregator'] == 'any') {
                        $all_value = (int)$value['value'];
                        $pointflag = $this->apiTraverseArray($value['conditions'], $product, $quoteId, $all_value, 'any');
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

    /**
     * @param $array
     * @param $product
     * @param bool $flag
     * @param string $allOrAny
     * @return int
     */
    public function traverseArray($array, $product, $flag = false, $allOrAny = '')
    {

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

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct(){
        return $this->productFactory->create();
    }
}
