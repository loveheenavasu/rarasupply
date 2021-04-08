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
namespace Ced\Rewardsystem\Model\Rule\Action;

class Product extends \Magento\Rule\Model\Action\AbstractAction
{
    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(['rule_price' => __('Rule price')]);
        return $this;
    }

    /**
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                'to_fixed' => __('To Fixed Value'),
                'to_percent' => __('To Percentage'),
                'by_fixed' => __('By Fixed value'),
                'by_percent' => __('By Percentage'),
            ]
        );
        return $this;
    }

    /**
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . __(
            "Update product's %1 %2: %3",
            $this->getAttributeElement()->getHtml(),
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml()
        );
        $html .= $this->getRemoveLinkHtml();
        return $html;
    }
}
