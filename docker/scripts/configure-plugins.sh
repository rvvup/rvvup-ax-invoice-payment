echo "Running configure-plugins.sh"

cd /bitnami/magento
bin/magento config:set payment/rvvup_ax_integration/active 1
