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
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Block\Po\Renderer;
 
class QuoteId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {
 

     public function __construct(
        \Magento\Backend\Block\Context $context,
     	\Ced\RequestToQuote\Model\QuoteFactory $quote,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->quote = $quote;
    }
	/**
	 * Render approval link in each vendor row
	 * @param Varien_Object $row
	 * @return String
	 */
	public function render(\Magento\Framework\DataObject $row) {
		$html = '';
		$quoteIncrementId = $this->quote->create()->load($row->getQuoteId())->getQuoteIncrementId();
		$url = $this->getUrl('rfq/quotes/view',['id'=>$row->getQuoteId()]);	
		$html .= '<a href="'.$url.'">'.$quoteIncrementId.'</a>';
		
		return $html;
	}
}