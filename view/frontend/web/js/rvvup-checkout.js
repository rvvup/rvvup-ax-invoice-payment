define(['jquery', 'Magento_Ui/js/modal/alert'], function($, alert) {
    'use strict';
    return {
        createAccountStatement: function (
            url,
            company_id,
            account_number
        ) {
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    'company_id' : company_id,
                    'account_number' : account_number
                },
                success: function (data) {
                    if (data.success !== true) {
                        document.getElementById('rvvup-payment').disabled = false;
                        alert({
                            content: 'We are unable to get this statement at the moment, please try again later'
                        });
                        return;
                    }

                    if (data['url']) {
                        window.location.href = data['url'];
                    } else {
                        document.getElementById('rvvup-payment').disabled = false;
                        alert({
                            content: 'We are unable to get this statement at the moment, please try again later'
                        });
                    }
                },
                error: function () {
                    document.getElementById('rvvup-payment').disabled = false;
                    alert({
                        content: 'We are unable to get this statement at the moment, please try again later'
                    });
                }
            })
        }
    };
});
