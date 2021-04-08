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
 * @author       CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/action/get-payment-information',
        'uiComponent',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/full-screen-loader'

    ],
    function ($, ko,getPaymentInformationAction, Component,totals, fullScreenLoader) {
        'use strict';
        var enterpoint = ko.observable(null);
        var isLoading = ko.observable(false);
        return Component.extend({
             defaults: {
                template: 'Ced_Rewardsystem/payment/redeem'
            },
            enterpoint: enterpoint,
            isLoading: isLoading,
            apply: function() {
                var defaultpoint = parseInt($('#defaultpoint').val());
                var baseUrl = redeem.baseUrl;
                var enterpoint = parseInt($('#enterpoint').val());
                
                var validpoint = this.totalPoint();
                
               
                if(validpoint == defaultpoint){
                    if(enterpoint>0){
                        if(enterpoint > defaultpoint){
                            $('#message').html('Enter point must be less than or equal to Total point');
                            return false;
                        }
                    }else{
                    	return false;
                    }
                }else{
                     $('#message').html('something went wrong');
                     return false;
                 }
                
                
                if(enterpoint != 0){
                	$('#defaultpoint').val(redeem.actualtotalPoint-enterpoint);
                                jQuery.ajax(
                              {   
                                url : baseUrl+'rewardsystem/rewardpoint/updateSubtotal',
                                type: "GET",
                                data : {
                                    enterpoint : enterpoint,
                                },
                                dataType: 'json',
                                showLoader:true,
                                success:function(data,status)
                               {
                                 redeem.totalPoint = redeem.actualtotalPoint-enterpoint;
                                 var deferred;
                                  deferred = $.Deferred();
                                  totals.isLoading(true);
                                  getPaymentInformationAction(deferred);
                                  $.when(deferred).done(function () {
                                    fullScreenLoader.stopLoader();
                                    totals.isLoading(false);
                                  }); 
                                 
                                 $('.action-remove').show();
                              
                              }
                         });
                        }
                else{
                     $('#message').html('Enter point must be greater than 0');
                }
                },
                remove: function() {
                $('#defaultpoint').val(redeem.actualtotalPoint);
                var baseUrl = redeem.baseUrl;
                var enterpoint = 0;
                
               
                              jQuery.ajax(
                              {   
                                url : baseUrl+'rewardsystem/rewardpoint/updateSubtotal',
                                type: "GET",
                                data : {
                                    enterpoint : enterpoint,
                                },
                                dataType: 'json',
                                showLoader:true,
                                success:function(data,status)
                              {
                                redeem.totalPoint = redeem.actualtotalPoint-enterpoint;
                                  var deferred;
                                  deferred = $.Deferred();
                                  totals.isLoading(true);
                                  getPaymentInformationAction(deferred);
                                  $.when(deferred).done(function () {
                                    fullScreenLoader.stopLoader();
                                    totals.isLoading(false);
                                  }); 
                                 
                                  $('.action-remove').hide();
                                  $('#enterpoint').val('');
                                  
                            }
                        });
                
                },
            customerLogin: function(){
              
                      if(redeem.customerLogin){
                            return redeem.customerLogin;
                        }else{
                                return false;
                             }
                        this.showRemovePoints();
                     },

            FillPoint:function() {
                    var enterpoint = parseInt($('#enterpoint').val());
                    var defaultpoint = parseInt($('#defaultpoint').val());
                    var validpoint = this.totalPoint();
                    if(validpoint == defaultpoint){
                        if(enterpoint>0){
                            if(enterpoint > defaultpoint){
                                $('#message').html('Enter point must be less than or equal to Total point');
                                return 0;
                            }
                            else{
                                $('#message').html('');
                                return enterpoint;

                            }

                        }else{
                             return 0;
                        }
                    }
                
            },
            totalPoint:function(){
               
                if(redeem.totalPoint){
                    return redeem.totalPoint;
                }else{
                    return false;
                }
            },
            
            actualtotalPoint:function(){
                
                if(redeem.actualtotalPoint){
                    return redeem.actualtotalPoint;
                }else{
                    return false;
                }
            },
            usedPoints: function(){
                if(redeem.currusedPoints){
                	 $('.action-remove').show();
                	return redeem.currusedPoints;
                	
               }else{
                       return '';
                    }
            },

            showRemovePoints:function(){

              if(this.usedPoints()){
               $('.action-remove').show();
              }
            },

        });
});
