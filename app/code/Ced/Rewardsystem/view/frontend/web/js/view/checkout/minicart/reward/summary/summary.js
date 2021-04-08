/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'uiComponent',
    ],
    function (ko,Component) {
        return Component.extend({
                     summaryPoint:function(){
                        if(rewardsummary.summaryPoint){
                            return rewardsummary.summaryPoint+'Points';
                        }else{
                            return false;
                        }
                    },
                    customerLogin: function(){
                         if(rewardsummary.customerLogin){
                            return rewardsummary.customerLogin;
                        }else{
                                return false;
                             }
                     },
                
            
                });

    });
