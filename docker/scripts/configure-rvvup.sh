echo "Running configure-rvvup.sh"

cd /bitnami/magento
rm -rf generated/

if [ "$RVVUP_PLUGIN_VERSION" == "local" ]; then
    # Run the command for "local"
    echo "Running local version setup..."
    mkdir -p app/code/Rvvup/AxInvoicePayment

else
    # Run the command for other values
    echo "Running setup for version: $RVVUP_RVVUP_PLUGIN_VERSION"
    composer require rvvup/module-ax-invoice-payment:$RVVUP_PLUGIN_VERSION
fi