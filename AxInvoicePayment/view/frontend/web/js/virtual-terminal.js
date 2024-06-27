define(['jquery', 'Magento_Ui/js/modal/alert'], function($, alert) {
    'use strict';
    return {
        createVirtualTerminal: function (
            url,
            amount,
            store_id,
            currency_code,
            invoices,
            selected_invoices,
            display_id
        ) {
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    'amount' : amount,
                    'store_id' : store_id,
                    'currency_code' : currency_code,
                    'invoices' : invoices,
                    'selected_invoices' : selected_invoices,
                    'display_id' : display_id
                },
                success: function (data) {
                    if (data.success !== true) {
                        document.getElementById('rvvup-ax-payment').disabled = false;
                        alert({
                            content: 'Failed to create payment, please message store owner'
                        });
                        return;
                    }

                    if (data['iframe-url']) {
                        let fetchCheckoutUrl = function () {
                            return Promise.resolve(
                                data['iframe-url']
                            );
                        }
                        const rvvup = Rvvup();
                        const checkout = rvvup.createEmbeddedCheckout({fetchCheckoutUrl})
                            .then((checkout) => {
                                    checkout.mount()
                                }
                            ).finally(() => {
                                document.getElementById('rvvup-ax-payment').disabled = false;
                            });
                    } else {
                        document.getElementById('rvvup-ax-payment').disabled = false;
                        alert({
                            content: 'Failed to create payment, please message store owner'
                        });
                    }
                },
                error: function () {
                    document.getElementById('rvvup-ax-payment').disabled = false;
                    alert({
                        content: 'Failed to create payment, please message store owner'
                    });
                }
            })
        }
    };
});
