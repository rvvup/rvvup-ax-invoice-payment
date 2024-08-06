echo "Running setup-rvvup.sh"

cd /bitnami/magento
echo "Installing rvvup/module-ax-invoice-payment:$RVVUP_PLUGIN_VERSION"
composer require rvvup/module-ax-invoice-payment:$RVVUP_PLUGIN_VERSION
