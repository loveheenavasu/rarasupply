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
 * @package     Ced_CsDeal
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Model;

/**
 * Class Deal
 * @package Ced\CsDeal\Model
 */
class Deal extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_APPROVED = '1';

    const STATUS_NOT_APPROVED = '0';

    const STATUS_PENDING = '2';

    protected static $_states;

    protected static $_statuses;

    /**
     * @var string
     */
    protected $_codeSeparator = '-';

    /**
     * @var DealFactory
     */
    protected $dealFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Deal constructor.
     * @param DealFactory $dealFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Ced\CsDeal\Model\DealFactory $dealFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->dealFactory = $dealFactory;
        $this->productFactory = $productFactory;
        parent::__construct($context, $registry);

    }

    protected function _construct()
    {
        $this->_init('Ced\CsDeal\Model\ResourceModel\Deal');
    }

    /**
     * @return array
     */
    public function getMassActionArray()
    {
        return array(
            '-1' => __(''),
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_NOT_APPROVED => __('Disapproved'),
            self::STATUS_PENDING => __('Approval Pending')
        );
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getVendorDealProductIds($id)
    {
        return $this->getResource()->getVendorDealProductIds($id);
    }

    /**
     * @param $dealId
     * @param $checkstatus
     * @return array
     * @throws \Exception
     */
    public function changeVdealStatus($dealId, $checkstatus)
    {
        $errors = array();
        if ($dealId) {
            $row = $this->dealFactory->create()->load($dealId);
            if ($row->getAdminStatus() != $checkstatus) {
                switch ($checkstatus) {
                    case \Ced\CsDeal\Model\Deal::STATUS_APPROVED:
                        $row->setAdminStatus(\Ced\CsDeal\Model\Deal::STATUS_APPROVED);
                        $errors['success'] = 1;
                        $product = $this->productFactory->create()->load($row->getProductId());
                        $product->setSpecialPrice($row->getDealPrice());
                        $product->setSpecialFromDate($row->getStartDate());
                        $product->setSpecialFromDateIsFormated(true);
                        $product->setSpecialToDate($row->getEndDate());
                        $product->setSpecialToDateIsFormated(true);
                        $product->save();
                        break;

                    case \Ced\CsDeal\Model\Deal::STATUS_NOT_APPROVED:
                        $row->setAdminStatus(\Ced\CsDeal\Model\Deal::STATUS_NOT_APPROVED);
                        $errors['success'] = 1;
                        $product = $this->productFactory->create()->load($row->getProductId());
                        $product->setSpecialPrice(null);
                        $product->getResource()->saveAttribute($product, 'special_price');
                        $product->save();
                        break;
                }
                $row->save();
            } else
                $errors['success'] = 1;
        } else {
            $errors['error'] = 1;
        }
        return $errors;
    }
}

?>