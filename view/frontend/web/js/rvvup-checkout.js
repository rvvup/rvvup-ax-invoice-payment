define(['jquery', 'Magento_Ui/js/modal/alert'], function($, alert) {
    'use strict';
    return {
        createAccountStatement: function (
            url,
            company_id,
            account_number,
            invoice_id,
            retryCount = 2
        ) {
            return new Promise(function (resolve, reject) {
                function createStatement() {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            'company_id': company_id,
                            'account_number': account_number,
                            'invoice_id': invoice_id
                        },
                        success: function (data) {
                            if (data.success) {
                                resolve(data);
                            } else {
                                if (retryCount > 0) {
                                    retryCount--;
                                    createStatement();
                                } else {
                                    resolve({ success: false });
                                }
                            }
                        },
                        error: function () {
                            if (retryCount > 0) {
                                retryCount--;
                                createStatement();
                            } else {
                                resolve({ success: false });
                            }
                        }
                    });
                }
                createStatement();
            });
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
