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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsDeal\Block\Edit\Tab\Renderer;

class Deal extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_vproduct;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Ced\CsMarketplace\Model\Vproducts $vproduct,
        array $data = []
    ) {
        $this->_vproduct = $vproduct;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
		$url=$this->getUrl('csdeal/deal/edit',array('deal_id'=>$row->getId()));
		return '<div id="dealbuttion">
				<button class="btn btn-warning" title="Create Deal" onclick="createDeal('.$row->getId().')" >Edit Deal</button>
				<input id="edit_url'.$row->getId().'" type="hidden" value="'.$url.'" />
				</div>
				<script>
				function createDeal(id){
					var element_id="edit_url"+id;
					var url= document.getElementById(element_id).value;
					
					document.location.href=url;
				}
				</script>';
	}
}