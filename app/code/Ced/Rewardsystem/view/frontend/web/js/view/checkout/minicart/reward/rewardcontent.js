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

define(
    [
        'ko',
        'uiComponent',
    ],
    function (ko,Component) {
        return Component.extend({
                    point:function()
                            {
                            jQuery.post(baseurl+'rewardsystem/rewardpoint/minicartpoint',
                            {   
                        
                                },function(data,status)
                            {    if(data){
                            	jQuery("#rewardpoint").html(data + ' Points');
                                }else{
                                	jQuery(".reward_point").html('');
                                }
                                
                            });
                            },
                    customerLogin: function(){
                         if(rewartpointdata.customerLogin){
                            return rewartpointdata.customerLogin;
                        }else{
                                return false;
                             }
                     },
                    urlRedirectLogin:function(){
                        if(rewartpointdata.urlRedirectLogin){
                            return rewartpointdata.urlRedirectLogin;
                        }else{
                            return false;
                        }
                    },
                    showPoint:function(){
                    if(rewartpointdata.showPoint == 1){
                            return true;
                        }
                    return false;
                    },
            
                });

    });
