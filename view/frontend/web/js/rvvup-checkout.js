define(['jquery', 'Magento_Ui/js/modal/alert'], function($, alert) {
    'use strict';
    return {
        createAccountStatement: function (
            url,
            company_id,
            account_number,
            invoice_id
        ) {
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    'company_id' : company_id,
                    'account_number' : account_number,
                    'invoice_id' : invoice_id
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
        },

        landingPage: function(url, company_id, account_number, invoice_id) {
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    'company_id': company_id,
                    'account_number': account_number,
                    'invoice_id': invoice_id,
                    'type': 'landing'
                }
            });
        },

        payClicked: function(url, company_id, account_number, invoice_id) {
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    'company_id': company_id,
                    'account_number': account_number,
                    'invoice_id': invoice_id,
                    'type': 'pay_clicked'
                }
            });
        }
    };
});
