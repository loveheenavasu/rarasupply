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
 * @package     Ced_CsRma
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */ 
namespace Ced\CsRma\Block\Adminhtml\AllRma;

class Add extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Initialize add review
     *
     * @return void
     */
	protected function _construct()
    {
        parent::_construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'Ced_CsRma';
        $this->_controller = 'adminhtml_allRma';
        $this->_mode = 'add';
        $this->buttonList->update('save', 'label', __('Save Request'));
        $this->buttonList->update('save', 'id', 'save_button');
        $this->buttonList->update('reset', 'id', 'reset_button');
        $this->_formScripts[] = '
            require(["prototype"], function(){
                toggleParentVis("add_rma_form");
                toggleVis("save_button");
                toggleVis("reset_button");
            });
        ';
        $this->_formInitScripts[] = '
            require(["jquery","prototype"], function(jQuery){
            window.review = function() {
                return {
                    orderInfoUrl : null,
                    formHidden : true,
                    gridRowClick : function(data, click) {    
                        if(Event.findElement(click,\'TR\').title){
                            review.orderInfoUrl = Event.findElement(click,\'TR\').title;
                            review.loadOrderData();
                            review.showForm();
                            review.formHidden = false;
                        }
                    },
                    loadOrderData : function() {
                        jQuery.ajax({
                            type: "POST",
                            url: review.orderInfoUrl,
                            data: {
                                form_key: FORM_KEY
                            },
                            showLoader: true,
                            success: review.reqSuccess,
                            error: review.reqFailure
                        });
                    },
                    showForm : function() {
                        toggleParentVis("add_rma_form");
                        toggleVis("grid_id");
                        toggleVis("save_button");
                        toggleVis("reset_button");
                    },
                    reqSuccess :function(response) {
                        if( response.error ) {
                            alert(response.message);
                        } else if( response.id ){
                            $("store_id").value = response.store_id;
                            $("store").innerHTML = response.store;
                            $("customer_id").value = response.customer_id;
                            $("product_data").innerHTML = response.product_data;
                            $("customer_detail").innerHTML = response.address.billing_address;
                            $("shipping_detail").innerHTML = response.address.shipping_address;
                            $("customer_email").innerHTML = response.address.customer_email;
                            $("group").innerHTML = response.group;
                            $("email").value = response.address.customer_email;
                            $("user-name").value = response.address.customer_name;
                            $("resolution_requested").innerHTML = response.resolution;
                            $("customer_name").innerHTML = \'<a href="' .
                                $this->getUrl(
                                    'customer/index/edit'
                                ) .
                                'id/\' + response.customer_id + \'" target="_blank">\' + response.address.customer_name + \'</a>\';

                            $("order_id").innerHTML = \'<a href="' .
                                $this->getUrl(
                                    'sales/order/view'
                                ) .
                                'order_id/\' + response.entity_id + \'" target="_blank">\' + response.increment_id + \'</a><input type="hidden" name="order_id" value="\'+response.increment_id+\'">\';

                        } else if ( response.message ) {
                            alert(response.message);
                        }
                    }
                }
            }();
            });
        //]]>
        '; 
    }
    //$this->groupRepository->getById($customerGroupId)->getCode();
    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $pageTitle = $this->getLayout()->createBlock('Ced\CsRma\Block\Adminhtml\AllRma\Header')->toHtml();
        if (is_object($this->getLayout()->getBlock('page.title'))) {
            $this->getLayout()->getBlock('page.title')->setPageTitle($pageTitle);
        }
        return parent::_prepareLayout();
    }

    /**
     * Prepare header html
     *
     * @return string
     */
    public function getHeaderHtml()
    {
        $out = '<div id="rma-header">' . $this->getLayout()->createBlock(
            'Ced\CsRma\Block\Adminhtml\AllRma\Header'
        )->toHtml() . '</div>';
        return $out;
    }

    /**
     * Get header width
     *
     * @return string
     */
    public function getHeaderWidth()
    {
        return 'width: 70%;';
    }
}