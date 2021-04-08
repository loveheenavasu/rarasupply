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
 * @package     Ced_CsDeal
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Block\Render;


use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Amountlist extends AbstractPrice 
//\Ced\CsMarketplace\Block\Vendor\AbstractBlock
//\Magento\Catalog\Block\Product\View extends AbstractPrice
{
    public $_coreRegistry;
const PRICE_CODE = 'special_price';

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $localeDate
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->localeDate = $localeDate;
    }

  
 public function showMinimalPrice()
    {
        /** @var Price\FinalPrice $finalPrice */
        $finalPrice = $this->getPriceType(Price\FinalPrice::PRICE_CODE);
        $finalPriceValue = $finalPrice->getAmount()->getValue();
        $minimalPriceAValue = $finalPrice->getMinimalPrice()->getValue();
        return $this->getDisplayMinimalPrice()
        && $minimalPriceAValue
        && $minimalPriceAValue < $finalPriceValue;
    }

 public function hasSpecialPrice()
    {
        $displayRegularPrice = $this->getPriceType(Price\RegularPrice::PRICE_CODE)->getAmount()->getValue();
        $displayFinalPrice = $this->getPriceType(Price\FinalPrice::PRICE_CODE)->getAmount()->getValue();
        return $displayFinalPrice < $displayRegularPrice;
    }

public function renderAmountMinimal()
    {
        /** @var \Magento\Catalog\Pricing\Price\FinalPrice $price */
        $price = $this->getPriceType(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE);
        $id = $this->getPriceId() ? $this->getPriceId() : 'product-minimal-price-' . $this->getSaleableItem()->getId();
        return $this->renderAmount(
            $price->getMinimalPrice(),
            [
                'display_label'     => __('As low as'),
                'price_id'          => $id,
                'include_container' => false,
                'skip_adjustments' => true
            ]
        );
    }



 protected function _toHtml()
    {
        if (!$this->getSaleableItem() || $this->getSaleableItem()->getCanShowPrice() === false) {
            return '';
        }

        $result = parent::_toHtml();

        try {
            /** @var MsrpPrice $msrpPriceType */
            $msrpPriceType = $this->getSaleableItem()->getPriceInfo()->getPrice('msrp_price');
        } catch (\InvalidArgumentException $e) {
            $this->_logger->critical($e);
            return $this->wrapResult($result);
        }

        //Renders MSRP in case it is enabled
        $product = $this->getSaleableItem();
        if ($msrpPriceType->canApplyMsrp($product) && $msrpPriceType->isMinimalPriceLessMsrp($product)) {
            /** @var BasePriceBox $msrpBlock */
            $msrpBlock = $this->rendererPool->createPriceRender(
                MsrpPrice::PRICE_CODE,
                $this->getSaleableItem(),
                [
                    'real_price_html' => $result,
                    'zone' => $this->getZone(),
                ]
            );
            $result = $msrpBlock->toHtml();
        }

        return $this->wrapResult($result);
    }

    /**
     * Wrap with standard required container
     *
     * @param string $html
     * @return string
     */
    protected function wrapResult($html)
    {
        return '<div class="price-box ' . $this->getData('css_classes') . '" ' .
            'data-role="priceBox" ' .
            'data-product-id="' . $this->getSaleableItem()->getId() . '"' .
            '>' . $html . '</div>';
    }

public function getValue()
    {
        if (null === $this->value) {
            $this->value = false;
            $specialPrice = $this->getSpecialPrice();
            if ($specialPrice !== null && $specialPrice !== false && $this->isScopeDateInInterval()) {
                $this->value = (float) $specialPrice;
            }
        }

        return $this->value;
    }


}


