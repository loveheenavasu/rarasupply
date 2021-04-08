function confirmSetLocation(message, url) {
    require(['Magento_Ui/js/modal/confirm'] , function (confirmation) {
        confirmation({
            title: "",
            content: message,
            actions: {
                confirm: function(){
                    window.location.href = url;
                }
            }
        });
    });
}
