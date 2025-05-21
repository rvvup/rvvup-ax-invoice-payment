define(['jquery', 'Magento_Ui/js/modal/alert'], function ($, alert) {
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
                                    reject();
                                }
                            }
                        },
                        error: function () {
                            if (retryCount > 0) {
                                retryCount--;
                                createStatement();
                            } else {
                                reject();
                            }
                        }
                    });
                }

                createStatement();
            });
        },

        landingPage: function (url, company_id, account_number, invoice_id, user_agent) {
            const qs = new URLSearchParams(location.search);
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    'company_id': company_id,
                    'account_number': account_number,
                    'invoice_id': invoice_id,
                    'type': 'landing',
                    source: qs.get('source'),
                    'user_agent': user_agent,
                }
            });
        },

        payClicked: function (url, company_id, account_number, invoice_id, user_agent) {
            const qs = new URLSearchParams(location.search);
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    'company_id': company_id,
                    'account_number': account_number,
                    'invoice_id': invoice_id,
                    'type': 'pay_clicked',
                    source: qs.get('source'),
                    'user_agent': user_agent,
                }
            });
        }
    };
});
